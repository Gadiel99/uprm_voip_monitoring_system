<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Building;
use App\Models\Devices;
use App\Models\Extensions;

/**
 * Reports Controller
 * 
 * Handles device search, filtering, and reporting functionality.
 * Provides aggregated statistics and detailed device information.
 */
class ReportsController extends Controller
{
    /**
     * Display the reports page with initial data.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        // Always load dropdown data + stats
        $buildings = Building::orderBy('name')->get(['building_id', 'name']);
        $stats = $this->getSystemStats();

        // If any filter present, perform search logic (delegate to shared builder)
        $devices = [];
        $filters = $request->only(['query']);
        $hasFilters = !empty($request->input('query'));

        if ($hasFilters) {
            [$devices, $filters] = $this->buildDevicesQuery($request);
        }

        return view('pages.reports', [
            'buildings' => $buildings,
            'stats' => $stats,
            'devices' => $devices,
            'filters' => $filters,
        ]);
    }
    
    /**
     * Handle device search with filters.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function search(Request $request)
    {
        // Legacy route: redirect to unified index with query params
        return redirect()->route('reports', $request->only(['query']));
    }
    
    /**
     * Get system overview statistics.
     *
     * @return array
     */
    private function getSystemStats(): array
    {
        $totalDevices = Devices::count();
        $activeDevices = Devices::where('status', 'online')->count();
        $inactiveDevices = Devices::where('status', 'offline')->count();
        $totalBuildings = Building::count();
        
        return [
            'total_devices' => $totalDevices,
            'active_devices' => $activeDevices,
            'inactive_devices' => $inactiveDevices,
            'total_buildings' => $totalBuildings,
        ];
    }
    
    /**
     * Group multiple extensions by device.
     *
     * @param  \Illuminate\Support\Collection  $devices
     * @return \Illuminate\Support\Collection
     */
    private function groupExtensionsByDevice($devices)
    {
        $grouped = collect();
        $deviceMap = [];
        
        foreach ($devices as $device) {
            // Cast to object to ensure type safety
            $device = (object) $device;
            $deviceId = $device->device_id;
            
            // If device already exists in map, append extension info
            if (isset($deviceMap[$deviceId])) {
                if (!empty($device->user_name)) {
                    $deviceMap[$deviceId]->extensions[] = [
                        'name' => $device->user_name,
                        'number' => $device->extension_number ?? null,
                    ];
                }
            } else {
                // Create new device entry
                $deviceEntry = (object)[
                    'device_id' => $device->device_id ?? null,
                    'mac_address' => $device->mac_address ?? null,
                    'ip_address' => $device->ip_address ?? null,
                    'status' => $device->status ?? 'unknown',
                    'is_critical' => $device->is_critical ?? false,
                    'building_name' => $device->building_name ?? 'Unassigned',
                    'building_id' => $device->building_id ?? null,
                    'extensions' => [],
                ];
                
                if (!empty($device->user_name)) {
                    $deviceEntry->extensions[] = [
                        'name' => $device->user_name,
                        'number' => $device->extension_number ?? null,
                    ];
                }
                
                $deviceMap[$deviceId] = $deviceEntry;
            }
        }
        
        return collect(array_values($deviceMap));
    }

    /**
     * Shared builder for devices query with filters.
     * Returns array: [devicesCollection, filtersArray]
     */
    private function buildDevicesQuery(Request $request): array
    {
        $validated = $request->validate([
            'query' => 'nullable|string|max:255',
        ]);

        $query = DB::table('devices as d')
            ->leftJoin('device_extensions as de', 'de.device_id', '=', 'd.device_id')
            ->leftJoin('extensions as e', 'e.extension_id', '=', 'de.extension_id')
            ->leftJoin('networks as n', 'n.network_id', '=', 'd.network_id')
            ->leftJoin('building_networks as bn', 'bn.network_id', '=', 'n.network_id')
            ->leftJoin('buildings as b', 'b.building_id', '=', 'bn.building_id')
            ->select(
                'd.device_id',
                'd.mac_address',
                'd.ip_address',
                'd.status',
                'd.is_critical',
                'b.name as building_name',
                'b.building_id',
                DB::raw("CONCAT_WS(' ', e.user_first_name, e.user_last_name) as user_name"),
                'e.extension_number'
            );

        if (!empty($validated['query'])) {
            $searchTerm = $validated['query'];
            
            // Split search terms by comma and trim each
            $searchTerms = array_filter(array_map('trim', explode(',', $searchTerm)));
            
            // Apply filters for each search term (ALL must match - AND logic)
            foreach ($searchTerms as $term) {
                // Normalize search term for MAC/IP matching (remove delimiters)
                $normalizedTerm = str_replace([':', '-', '.', ' '], '', $term);
                
                $query->where(function ($q) use ($term, $normalizedTerm) {
                    // Search in user names
                    $q->where('e.user_first_name', 'like', "%$term%")
                      ->orWhere('e.user_last_name', 'like', "%$term%")
                      ->orWhere(DB::raw("CONCAT(e.user_first_name, ' ', e.user_last_name)"), 'like', "%$term%")
                      // Search in MAC address (normalized)
                      ->orWhere(DB::raw("REPLACE(REPLACE(REPLACE(d.mac_address, ':', ''), '-', ''), '.', '')"), 'like', "%$normalizedTerm%")
                      // Search in IP address (normalized)
                      ->orWhere(DB::raw("REPLACE(d.ip_address, '.', '')"), 'like', "%$normalizedTerm%")
                      // Search in status
                      ->orWhere('d.status', 'like', "%$term%")
                      // Search in building name
                      ->orWhere('b.name', 'like', "%$term%")
                      // Search in extension number
                      ->orWhere('e.extension_number', 'like', "%$term%");
                });
            }
        }

        $rawDevices = $query->distinct()->get();
        $devices = $this->groupExtensionsByDevice($rawDevices);

        return [$devices, $validated];
    }
}
