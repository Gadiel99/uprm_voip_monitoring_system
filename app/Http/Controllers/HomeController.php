<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AlertSettings;

/**
 * Home/Dashboard Controller
 * 
 * Displays the main dashboard with building status colors based on
 * offline device percentage thresholds.
 */
class HomeController extends Controller
{
    /**
     * Display the home/dashboard page with building statuses.
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
            ->orderBy('b.name')
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
                return $building;
            });

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

        // Count buildings by alert level
        $buildingCounts = [
            'green' => $buildings->where('alert_level', 'green')->count(),
            'yellow' => $buildings->where('alert_level', 'yellow')->count(),
            'red' => $buildings->where('alert_level', 'red')->count(),
        ];

        // Prepare stats array for System Overview
        $stats = [
            'total_devices' => $systemSummary->total_devices ?? 0,
            'active_devices' => $systemSummary->online_devices ?? 0,
            'inactive_devices' => $systemSummary->offline_devices ?? 0,
            'total_buildings' => $buildings->count(),
        ];

        return view('pages.home', [
            'buildings' => $buildings,
            'systemSummary' => $systemSummary,
            'buildingCounts' => $buildingCounts,
            'alertSettings' => $alertSettings,
            'stats' => $stats,
        ]);
    }
}
