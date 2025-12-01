<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AlertSettings;

/**
 * Alerts Controller
 * 
 * Displays building/device status with color-coded alerts based on
 * offline device percentage thresholds configured in admin settings.
 */
class AlertsController extends Controller
{
    /**
     * Display alerts page with building statuses.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $alertSettings = AlertSettings::current();

        // Get buildings with device counts and offline percentages
        $buildings = DB::table('buildings as b')
            ->leftJoin('building_networks as bn', 'bn.building_id', '=', 'b.building_id')
            ->leftJoin('networks as n', 'n.network_id', '=', 'bn.network_id')
            ->leftJoin('devices as d', 'd.network_id', '=', 'n.network_id')
            ->groupBy('b.building_id', 'b.name')
            ->selectRaw("
                b.building_id,
                b.name,
                COUNT(DISTINCT d.device_id) as total_devices,
                SUM(CASE WHEN d.status = 'online' THEN 1 ELSE 0 END) as online_devices,
                SUM(CASE WHEN d.status = 'offline' THEN 1 ELSE 0 END) as offline_devices,
                CASE 
                    WHEN COUNT(DISTINCT d.device_id) > 0 
                    THEN ROUND((SUM(CASE WHEN d.status = 'offline' THEN 1 ELSE 0 END) * 100.0) / COUNT(DISTINCT d.device_id), 1)
                    ELSE 0
                END as offline_percentage
            ")
            ->get()
            ->map(function($building) use ($alertSettings) {
                $building->alert_level = $alertSettings->getAlertLevel($building->offline_percentage);
                // Add severity order for sorting: red=1, yellow=2, green=3
                $building->severity_order = $building->alert_level === 'red' ? 1 : 
                                          ($building->alert_level === 'yellow' ? 2 : 3);
                return $building;
            })
            // Sort by severity first (Critical → Warning → Normal), then alphabetically
            ->sortBy([
                ['severity_order', 'asc'],
                ['name', 'asc']
            ])
            ->values(); // Reset array keys after sorting

        // Critical devices summary
        $criticalDevices = DB::table('devices')
            ->where('is_critical', true)
            ->selectRaw("
                COUNT(*) as total_devices,
                SUM(CASE WHEN status = 'online' THEN 1 ELSE 0 END) as online_devices,
                SUM(CASE WHEN status = 'offline' THEN 1 ELSE 0 END) as offline_devices,
                CASE 
                    WHEN COUNT(*) > 0 
                    THEN ROUND((SUM(CASE WHEN status = 'offline' THEN 1 ELSE 0 END) * 100.0) / COUNT(*), 1)
                    ELSE 0
                END as offline_percentage
            ")
            ->first();

        if ($criticalDevices) {
            $criticalDevices->alert_level = $alertSettings->getAlertLevel($criticalDevices->offline_percentage);
        }

        // System-wide summary
        $systemSummary = DB::table('devices')
            ->selectRaw("
                COUNT(*) as total_devices,
                SUM(CASE WHEN status = 'online' THEN 1 ELSE 0 END) as online_devices,
                SUM(CASE WHEN status = 'offline' THEN 1 ELSE 0 END) as offline_devices,
                CASE 
                    WHEN COUNT(*) > 0 
                    THEN ROUND((SUM(CASE WHEN status = 'offline' THEN 1 ELSE 0 END) * 100.0) / COUNT(*), 1)
                    ELSE 0
                END as offline_percentage
            ")
            ->first();

        if ($systemSummary) {
            $systemSummary->alert_level = $alertSettings->getAlertLevel($systemSummary->offline_percentage);
        }

        return view('pages.alerts', [
            'alertSettings' => $alertSettings,
            'buildings' => $buildings,
            'criticalDevices' => $criticalDevices,
            'systemSummary' => $systemSummary,
        ]);
    }

    /**
     * Display offline devices for a specific building (from alerts page).
     *
     * @param  int|string $buildingId
     * @return \Illuminate\Contracts\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function offlineDevices($buildingId)
    {
        // Building info
        $building = DB::table('buildings')->where('building_id', $buildingId)->first();
        abort_if(!$building, 404);

        // Get offline devices for this building with network subnet (paginated)
        $devices = DB::table('devices as d')
            ->join('networks as n', 'n.network_id', '=', 'd.network_id')
            ->join('building_networks as bn', 'bn.network_id', '=', 'n.network_id')
            ->where('bn.building_id', $buildingId)
            ->where('d.status', 'offline')
            ->orderBy('n.subnet')
            ->orderBy('d.ip_address')
            ->select('d.device_id', 'd.ip_address', 'd.mac_address', 'n.subnet')
            ->paginate(10);

        // Get extensions for these offline devices
        $extByDevice = $devices->isEmpty()
            ? collect()
            : DB::table('device_extensions as de')
                ->join('extensions as e', 'e.extension_id', '=', 'de.extension_id')
                ->whereIn('de.device_id', $devices->pluck('device_id'))
                ->select('de.device_id', 'e.extension_number', 'e.user_first_name', 'e.user_last_name')
                ->get()
                ->groupBy('device_id');

        return view('pages.offline_devices_by_building', [
            'building'    => $building,
            'devices'     => $devices,
            'extByDevice' => $extByDevice,
        ]);
    }

    /**
     * Display only offline critical devices (from alerts page).
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function criticalOffline()
    {
        // Get only offline critical devices (paginated)
        $devices = DB::table('devices as d')
            ->where('d.is_critical', true)
            ->where('d.status', 'offline')
            ->leftJoin('networks as n', 'n.network_id', '=', 'd.network_id')
            ->orderBy('d.ip_address')
            ->select('d.device_id', 'd.ip_address', 'd.mac_address', 'd.status', 'd.owner', 'n.subnet')
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

        return view('pages.critical_offline_devices', [
            'devices'     => $devices,
            'extByDevice' => $extByDevice,
        ]);
    }
}
