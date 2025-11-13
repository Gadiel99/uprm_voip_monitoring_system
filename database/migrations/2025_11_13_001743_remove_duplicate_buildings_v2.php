<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find and remove duplicate buildings, keeping the one with map coordinates
        $duplicates = DB::table('buildings')
            ->select('name', DB::raw('COUNT(*) as count'))
            ->groupBy('name')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            // Get all buildings with this name
            $buildings = DB::table('buildings')
                ->where('name', $duplicate->name)
                ->orderBy('map_x', 'desc')
                ->orderBy('map_y', 'desc')
                ->orderBy('building_id', 'asc')
                ->get();

            // Keep the first one (with coordinates if available), delete the rest
            $keepId = $buildings->first()->building_id;
            $deleteIds = $buildings->slice(1)->pluck('building_id');

            foreach ($deleteIds as $deleteId) {
                // Move any network relationships to the kept building
                $networkRelations = DB::table('building_networks')
                    ->where('building_id', $deleteId)
                    ->get();

                foreach ($networkRelations as $relation) {
                    // Check if this network relationship already exists for the kept building
                    $exists = DB::table('building_networks')
                        ->where('building_id', $keepId)
                        ->where('network_id', $relation->network_id)
                        ->exists();

                    if (!$exists) {
                        // Move the relationship
                        DB::table('building_networks')
                            ->where('building_id', $deleteId)
                            ->where('network_id', $relation->network_id)
                            ->update(['building_id' => $keepId]);
                    } else {
                        // Delete duplicate relationship
                        DB::table('building_networks')
                            ->where('building_id', $deleteId)
                            ->where('network_id', $relation->network_id)
                            ->delete();
                    }
                }

                // Delete the duplicate building
                DB::table('buildings')->where('building_id', $deleteId)->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse automatic duplicate removal
    }
};
