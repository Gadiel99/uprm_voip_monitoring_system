<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controlador de Dispositivos.
 *
 * - index(): resumen por edificio con conteos online/offline (joins a través de pivotes).
 * - byBuilding(): detalle por edificio con dispositivos y extensiones asociadas.
 */
class DevicesController extends Controller
{
    /**
     * Resumen de edificios y conteos de dispositivos (online/offline).
     *
     * Joins:
     * buildings -> building_networks -> networks -> devices
     * + extensiones agrupadas por building.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // Trae TODOS los edificios sin duplicados
        $overview = DB::table('buildings as b')
            ->leftJoin('building_networks as bn', 'bn.building_id', '=', 'b.building_id')
            ->leftJoin('networks as n', 'n.network_id', '=', 'bn.network_id')
            ->leftJoin('devices as d', 'd.network_id', '=', 'n.network_id')
            ->groupBy('b.building_id')
            ->orderBy('b.name')
            ->selectRaw("
                b.building_id,
                MAX(b.name) as name,
                COUNT(DISTINCT d.device_id) as total_devices,
                SUM(CASE WHEN d.status = 'online' THEN 1 ELSE 0 END) as online_devices,
                SUM(CASE WHEN d.status = 'offline' THEN 1 ELSE 0 END) as offline_devices
            ")
            ->get();

        // Extensiones por edificio (vía building_networks → networks → devices → device_extensions → extensions)
        $extensionsByBuilding = DB::table('extensions as e')
            ->join('device_extensions as de', 'de.extension_id', '=', 'e.extension_id')
            ->join('devices as d', 'd.device_id', '=', 'de.device_id')
            ->join('networks as n', 'n.network_id', '=', 'd.network_id')
            ->join('building_networks as bn', 'bn.network_id', '=', 'n.network_id')
            ->select('bn.building_id', 'e.extension_number', 'e.user_first_name', 'e.user_last_name')
            ->distinct()
            ->get()
            ->groupBy('building_id');

        // Critical devices count
        $criticalDevices = DB::table('devices')
            ->where('is_critical', true)
            ->selectRaw("
                COUNT(*) as total_devices,
                SUM(CASE WHEN status = 'online' THEN 1 ELSE 0 END) as online_devices,
                SUM(CASE WHEN status = 'offline' THEN 1 ELSE 0 END) as offline_devices
            ")
            ->first();

        return view('pages.devices', [
            'overview' => $overview,
            'extensionsByBuilding' => $extensionsByBuilding,
            'criticalDevices' => $criticalDevices,
        ]);
    }

    /**
     * Detalle de dispositivos por edificio.
     *
     * Joins:
     * devices -> networks -> building_networks (por building_id)
     * + extensiones agrupadas por device.
     *
     * @param  int|string $buildingId
     * @return \Illuminate\Contracts\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function byBuilding($buildingId)
    {
        // Info del edificio
        $building = DB::table('buildings')->where('building_id', $buildingId)->first();
        abort_if(!$building, 404);

        // Devices del building (join por el pivot)
        $devices = DB::table('devices as d')
            ->join('networks as n', 'n.network_id', '=', 'd.network_id')
            ->join('building_networks as bn', 'bn.network_id', '=', 'n.network_id')
            ->where('bn.building_id', $buildingId)
            ->orderBy('d.ip_address')
            ->select('d.device_id', 'd.ip_address', 'd.mac_address', 'd.status', 'd.is_critical', 'd.network_id')
            ->get();

        // Extensiones por device
        $extByDevice = $devices->isEmpty()
            ? collect()
            : DB::table('device_extensions as de')
                ->join('extensions as e', 'e.extension_id', '=', 'de.extension_id')
                ->whereIn('de.device_id', $devices->pluck('device_id'))
                ->select('de.device_id', 'e.extension_number', 'e.user_first_name', 'e.user_last_name')
                ->get()
                ->groupBy('device_id');

        return view('pages.devices_by_building', [
            'building'    => $building,
            'devices'     => $devices,
            'extByDevice' => $extByDevice,
        ]);
    }

    /**
     * Display critical devices.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function criticalDevices()
    {
        // Get all critical devices
        $devices = DB::table('devices as d')
            ->where('d.is_critical', true)
            ->orderBy('d.ip_address')
            ->select('d.device_id', 'd.ip_address', 'd.mac_address', 'd.status', 'd.is_critical', 'd.network_id')
            ->get();

        // Extensiones por device
        $extByDevice = $devices->isEmpty()
            ? collect()
            : DB::table('device_extensions as de')
                ->join('extensions as e', 'e.extension_id', '=', 'de.extension_id')
                ->whereIn('de.device_id', $devices->pluck('device_id'))
                ->select('de.device_id', 'e.extension_number', 'e.user_first_name', 'e.user_last_name')
                ->get()
                ->groupBy('device_id');

        // Create a mock building object for critical devices
        $building = (object) [
            'building_id' => 0,
            'name' => 'Critical Devices'
        ];

        return view('pages.devices_by_building', [
            'building'    => $building,
            'devices'     => $devices,
            'extByDevice' => $extByDevice,
        ]);
    }
}
