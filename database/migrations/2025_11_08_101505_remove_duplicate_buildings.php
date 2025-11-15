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
        // Find and remove duplicate buildings, keeping only the one with the lower ID
        $duplicateBuildings = DB::table('buildings')
            ->select('name', DB::raw('MIN(building_id) as keep_id'), DB::raw('GROUP_CONCAT(building_id ORDER BY building_id) as all_ids'))
            ->groupBy('name')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicateBuildings as $duplicate) {
            $allIds = explode(',', $duplicate->all_ids);
            $keepId = $duplicate->keep_id;
            
            // Get IDs to delete (all except the one we're keeping)
            $deleteIds = array_filter($allIds, fn($id) => $id != $keepId);
            
            echo "Processing duplicate: {$duplicate->name}\n";
            echo "  Keeping ID: {$keepId}\n";
            echo "  Deleting IDs: " . implode(', ', $deleteIds) . "\n";
            
            foreach ($deleteIds as $deleteId) {
                // Get all network relationships for the duplicate building
                $networkRelations = DB::table('building_networks')
                    ->where('building_id', $deleteId)
                    ->get();
                
                foreach ($networkRelations as $relation) {
                    // Check if this relationship already exists for the kept building
                    $exists = DB::table('building_networks')
                        ->where('building_id', $keepId)
                        ->where('network_id', $relation->network_id)
                        ->exists();
                    
                    if (!$exists) {
                        // Relationship doesn't exist, update it
                        DB::table('building_networks')
                            ->where('building_id', $deleteId)
                            ->where('network_id', $relation->network_id)
                            ->update(['building_id' => $keepId]);
                    } else {
                        // Relationship already exists, just delete the duplicate
                        DB::table('building_networks')
                            ->where('building_id', $deleteId)
                            ->where('network_id', $relation->network_id)
                            ->delete();
                    }
                }
                
                // Delete the duplicate building
                DB::table('buildings')
                    ->where('building_id', $deleteId)
                    ->delete();
            }
        }
        
        echo "Duplicate buildings removed successfully.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse this migration as we don't know which buildings were duplicates
        echo "This migration cannot be reversed.\n";
    }
};
