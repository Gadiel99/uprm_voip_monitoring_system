<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Helpers\SystemLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BuildingController extends Controller
{
    /**
     * Get all buildings with their networks
     */
    public function index()
    {
        $buildings = Building::with('networks')->get();
        
        return response()->json($buildings);
    }

    /**
     * Store a new building
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'map_x' => 'required|numeric|min:0|max:100',
            'map_y' => 'required|numeric|min:0|max:100',
            'networks' => 'required|array|min:1',
            'networks.*' => 'required|string|ip' // Each network must be a valid IP
        ]);

        try {
            DB::beginTransaction();

            // Create building
            $building = Building::create([
                'name' => strip_tags($validated['name']),
                'map_x' => $validated['map_x'],
                'map_y' => $validated['map_y']
            ]);

            // Create or attach networks
            foreach ($validated['networks'] as $networkSubnet) {
                // Check if network exists
                $network = DB::table('networks')
                    ->where('subnet', $networkSubnet)
                    ->first();

                if (!$network) {
                    // Create new network
                    $networkId = DB::table('networks')->insertGetId([
                        'subnet' => $networkSubnet,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $networkId = $network->network_id;
                }

                // Attach network to building (no timestamps in pivot table)
                DB::table('building_networks')->insert([
                    'building_id' => $building->building_id,
                    'network_id' => $networkId
                ]);
            }

            DB::commit();

            // Log the action
            SystemLogger::logAdd(
                'Building',
                $building->building_id,
                [
                    'name' => $building->name,
                    'networks' => $validated['networks'],
                    'coordinates' => ['x' => $building->map_x, 'y' => $building->map_y]
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Building '{$building->name}' created successfully",
                'building' => $building->load('networks')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            SystemLogger::logError('Failed to create building', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create building: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a building
     */
    public function update(Request $request, $id)
    {
        $building = Building::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'map_x' => 'sometimes|numeric|min:0|max:100',
            'map_y' => 'sometimes|numeric|min:0|max:100',
            'networks' => 'sometimes|array|min:1',
            'networks.*' => 'string|ip'
        ]);

        try {
            DB::beginTransaction();

            $oldData = $building->toArray();

            // Update building info
            if (isset($validated['name'])) {
                $building->name = strip_tags($validated['name']);
            }
            if (isset($validated['map_x'])) {
                $building->map_x = $validated['map_x'];
            }
            if (isset($validated['map_y'])) {
                $building->map_y = $validated['map_y'];
            }

            $building->save();

            // Update networks if provided
            if (isset($validated['networks'])) {
                // Remove old network associations
                DB::table('building_networks')
                    ->where('building_id', $building->building_id)
                    ->delete();

                // Add new networks
                foreach ($validated['networks'] as $networkSubnet) {
                    $network = DB::table('networks')
                        ->where('subnet', $networkSubnet)
                        ->first();

                    if (!$network) {
                        $networkId = DB::table('networks')->insertGetId([
                            'subnet' => $networkSubnet,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    } else {
                        $networkId = $network->network_id;
                    }

                    DB::table('building_networks')->insert([
                        'building_id' => $building->building_id,
                        'network_id' => $networkId
                    ]);
                }
            }

            DB::commit();

            SystemLogger::logEdit(
                'Building',
                $building->building_id,
                [
                    'old' => $oldData,
                    'new' => $building->toArray()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Building '{$building->name}' updated successfully",
                'building' => $building->load('networks')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            SystemLogger::logError('Failed to update building', [
                'building_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update building: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a building
     */
    public function destroy($id)
    {
        try {
            $building = Building::findOrFail($id);
            $buildingName = $building->name;

            DB::beginTransaction();

            // Delete network associations
            DB::table('building_networks')
                ->where('building_id', $building->building_id)
                ->delete();

            // Delete building
            $building->delete();

            DB::commit();

            SystemLogger::logDelete('Building', $id, [
                'name' => $buildingName
            ]);

            return response()->json([
                'success' => true,
                'message' => "Building '{$buildingName}' deleted successfully"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            SystemLogger::logError('Failed to delete building', [
                'building_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete building: ' . $e->getMessage()
            ], 500);
        }
    }
}
