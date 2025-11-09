<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Buildings;
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
        $buildings = Buildings::orderBy('name')->get(['building_id', 'name']);
        $stats = $this->getSystemStats();

        // If any filter present, perform search logic (delegate to shared builder)
        $devices = [];
        $filters = $request->only(['user','mac','ip','status','building_id']);
        $hasFilters = collect($filters)->filter(fn($v) => !is_null($v) && $v !== '')->isNotEmpty();

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
     * @return \Illuminate\Contracts\View\View
     */
    public function search(Request $request)
    {
        // Legacy route: redirect to unified index with query params
        return redirect()->route('reports', $request->only(['user','mac','ip','status','building_id']));
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
        $totalBuildings = Buildings::count();
        
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
            $deviceId = $device->device_id;
            
            // If device already exists in map, append extension info
            if (isset($deviceMap[$deviceId])) {
                if ($device->user_name) {
                    $deviceMap[$deviceId]->extensions[] = [
                        'name' => $device->user_name,
                        'number' => $device->extension_number,
                    ];
                }
            } else {
                // Create new device entry
                $deviceEntry = (object)[
                    'device_id' => $device->device_id,
                    'mac_address' => $device->mac_address,
                    'ip_address' => $device->ip_address,
                    'status' => $device->status,
                    'is_critical' => $device->is_critical,
                    'building_name' => $device->building_name ?? 'Unassigned',
                    'building_id' => $device->building_id,
                    'extensions' => [],
                ];
                
                if ($device->user_name) {
                    $deviceEntry->extensions[] = [
                        'name' => $device->user_name,
                        'number' => $device->extension_number,
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
            'user' => 'nullable|string|max:255',
            'mac' => 'nullable|string|max:32', // allow longer partials
            'ip' => 'nullable|string|max:45',  // avoid forcing strict ip validation to allow partials
            'status' => 'nullable|in:online,offline',
            'building_id' => 'nullable|exists:buildings,building_id',
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

        if (!empty($validated['user'])) {
            $userTerm = $validated['user'];
            $query->where(function ($q) use ($userTerm) {
                $q->where('e.user_first_name', 'like', "%$userTerm%")
                  ->orWhere('e.user_last_name', 'like', "%$userTerm%")
                  ->orWhere(DB::raw("CONCAT(e.user_first_name, ' ', e.user_last_name)"), 'like', "%$userTerm%");
            });
        }
        if (!empty($validated['mac'])) {
            $query->where('d.mac_address', 'like', "%{$validated['mac']}%");
        }
        if (!empty($validated['ip'])) {
            // allow partial ip match
            $query->where('d.ip_address', 'like', "%{$validated['ip']}%");
        }
        if (!empty($validated['status'])) {
            $query->where('d.status', $validated['status']);
        }
        if (!empty($validated['building_id'])) {
            $query->where('b.building_id', $validated['building_id']);
        }

        $rawDevices = $query->distinct()->get();
        $devices = $this->groupExtensionsByDevice($rawDevices);

        return [$devices, $validated];
    }
}
