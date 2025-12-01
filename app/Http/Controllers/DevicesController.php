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
        // Trae TODOS los edificios sin duplicados + total de redes asociadas
        $overview = DB::table('buildings as b')
            ->leftJoin('building_networks as bn', 'bn.building_id', '=', 'b.building_id')
            ->leftJoin('networks as n', 'n.network_id', '=', 'bn.network_id')
            ->leftJoin('devices as d', 'd.network_id', '=', 'n.network_id')
            ->groupBy('b.building_id')
            ->orderBy('b.name')
            ->selectRaw("
                b.building_id,
                MAX(b.name) as name,
                COUNT(DISTINCT n.network_id) as total_networks,
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

        // Critical devices count + distinct networks
        $criticalDevices = DB::table('devices as d')
            ->leftJoin('networks as n', 'n.network_id', '=', 'd.network_id')
            ->where('d.is_critical', true)
            ->selectRaw("
                COUNT(DISTINCT n.network_id) as total_networks,
                COUNT(*) as total_devices,
                SUM(CASE WHEN d.status = 'online' THEN 1 ELSE 0 END) as online_devices,
                SUM(CASE WHEN d.status = 'offline' THEN 1 ELSE 0 END) as offline_devices
            ")
            ->first();

        // Action Required: Networks not assigned to any building
        // Get only networks that are NOT in building_networks table
        // Use a subquery to exclude networks that exist in building_networks
        $unmappedNetworks = DB::table('networks as n')
            ->leftJoin('devices as d', 'd.network_id', '=', 'n.network_id')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('building_networks as bn')
                      ->whereRaw('bn.network_id = n.network_id');
            })
            ->groupBy('n.network_id', 'n.subnet')
            ->selectRaw("
                n.network_id,
                n.subnet,
                COUNT(DISTINCT d.device_id) as total_devices
            ")
            ->get();

        $unmappedStats = (object) [
            'total_networks' => $unmappedNetworks->count(),
            'total_devices' => $unmappedNetworks->sum('total_devices')
        ];

        return view('pages.devices', [
            'overview' => $overview,
            'extensionsByBuilding' => $extensionsByBuilding,
            'criticalDevices' => $criticalDevices,
            'unmappedNetworks' => $unmappedNetworks,
            'unmappedStats' => $unmappedStats,
        ]);
    }

    /**
     * Show networks for a specific building (intermediate layer).
     *
     * @param  int|string $buildingId
     * @return \Illuminate\Contracts\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function byBuilding($buildingId)
    {
        // Building info
        $building = DB::table('buildings')->where('building_id', $buildingId)->first();
        abort_if(!$building, 404);

        // Get all networks for this building
        $networks = DB::table('networks as n')
            ->join('building_networks as bn', 'bn.network_id', '=', 'n.network_id')
            ->where('bn.building_id', $buildingId)
            ->orderBy('n.subnet')
            ->select('n.subnet')
            ->distinct()
            ->pluck('subnet');

        // Get devices grouped by network for this building
        $devicesByNetwork = collect();
        foreach ($networks as $network) {
            $devices = DB::table('devices as d')
                ->join('networks as n', 'n.network_id', '=', 'd.network_id')
                ->join('building_networks as bn', 'bn.network_id', '=', 'n.network_id')
                ->where('bn.building_id', $buildingId)
                ->where('n.subnet', $network)
                ->select('d.device_id', 'd.ip_address', 'd.status')
                ->get();
            
            $devicesByNetwork->put($network, $devices);
        }

        return view('pages.devices_by_building', [
            'building'    => $building,
            'networks'    => $networks,
            'devicesByNetwork' => $devicesByNetwork,
        ]);
    }

    /**
     * Show devices for a specific network within a building.
     *
     * @param  int|string $buildingId
     * @param  string $network
     * @return \Illuminate\Contracts\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function byNetwork($buildingId, $network)
    {
        // Building info
        $building = DB::table('buildings')->where('building_id', $buildingId)->first();
        abort_if(!$building, 404);

        // Decode network parameter (in case it's URL encoded)
        $network = urldecode($network);

        // Get devices for this specific network in this building (paginated)
        $devices = DB::table('devices as d')
            ->join('networks as n', 'n.network_id', '=', 'd.network_id')
            ->join('building_networks as bn', 'bn.network_id', '=', 'n.network_id')
            ->where('bn.building_id', $buildingId)
            ->where('n.subnet', $network)
            ->orderBy('d.ip_address')
            ->select('d.device_id', 'd.ip_address', 'd.mac_address', 'd.status', 'd.is_critical', 'd.network_id')
            ->paginate(10);

        // Get extensions for these devices
        $extByDevice = $devices->isEmpty()
            ? collect()
            : DB::table('device_extensions as de')
                ->join('extensions as e', 'e.extension_id', '=', 'de.extension_id')
                ->whereIn('de.device_id', $devices->pluck('device_id'))
                ->select('de.device_id', 'e.extension_number', 'e.user_first_name', 'e.user_last_name')
                ->get()
                ->groupBy('device_id');

        return view('pages.devices_in_network', [
            'building'    => $building,
            'network'     => $network,
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

        return view('pages.critical_devices', [
            'devices'     => $devices,
            'extByDevice' => $extByDevice,
        ]);
    }

    /**
     * Display unmapped networks (not assigned to any building).
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function unmapped()
    {
        // Get all networks NOT assigned to any building using subquery
        $networks = DB::table('networks as n')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('building_networks as bn')
                      ->whereRaw('bn.network_id = n.network_id');
            })
            ->orderBy('n.subnet')
            ->select('n.network_id', 'n.subnet')
            ->distinct()
            ->get();

        // Get devices grouped by network
        $devicesByNetwork = collect();
        foreach ($networks as $network) {
            $devices = DB::table('devices as d')
                ->where('d.network_id', $network->network_id)
                ->select('d.device_id', 'd.ip_address', 'd.status')
                ->get();
            
            $devicesByNetwork->put($network->subnet, $devices);
        }

        // Create a mock building object
        $building = (object) [
            'building_id' => null,
            'name' => 'Need Connection'
        ];

        return view('pages.unmapped_networks', [
            'building'    => $building,
            'networks'    => $networks->pluck('subnet'),
            'devicesByNetwork' => $devicesByNetwork,
        ]);
    }

    /**
     * Display devices in an unmapped network.
     *
     * @param  string $network
     * @return \Illuminate\Contracts\View\View
     */
    public function unmappedNetwork($network)
    {
        // Decode network parameter
        $network = urldecode($network);

        // Get the network record
        $networkRecord = DB::table('networks as n')
            ->where('n.subnet', $network)
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('building_networks as bn')
                      ->whereRaw('bn.network_id = n.network_id');
            })
            ->first();

        abort_if(!$networkRecord, 404, 'Network not found or already assigned to a building');

        // Get devices for this network (paginated)
        $devices = DB::table('devices as d')
            ->where('d.network_id', $networkRecord->network_id)
            ->orderBy('d.ip_address')
            ->select('d.device_id', 'd.ip_address', 'd.mac_address', 'd.status', 'd.is_critical', 'd.network_id')
            ->paginate(10);

        // Get extensions for these devices
        $extByDevice = $devices->isEmpty()
            ? collect()
            : DB::table('device_extensions as de')
                ->join('extensions as e', 'e.extension_id', '=', 'de.extension_id')
                ->whereIn('de.device_id', $devices->pluck('device_id'))
                ->select('de.device_id', 'e.extension_number', 'e.user_first_name', 'e.user_last_name')
                ->get()
                ->groupBy('device_id');

        // Create a mock building object
        $building = (object) [
            'building_id' => null,
            'name' => 'Need Connection'
        ];

        return view('pages.unmapped_network_devices', [
            'building'    => $building,
            'network'     => $network,
            'devices'     => $devices,
            'extByDevice' => $extByDevice,
        ]);
    }
}

