<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugNetworks extends Command
{
    protected $signature = 'debug:networks';
    protected $description = 'Debug networks assignment';

    public function handle()
    {
        $this->info('=== DEBUG NETWORKS ===');
        $this->newLine();

        // Total networks
        $totalNetworks = DB::table('networks')->count();
        $this->info("Total networks in database: $totalNetworks");
        $this->newLine();

        // Networks assigned to buildings
        $assignedNetworkIds = DB::table('building_networks')
            ->distinct()
            ->pluck('network_id')
            ->toArray();
        
        $this->info("Network IDs assigned to buildings: " . count($assignedNetworkIds));
        $this->line(json_encode($assignedNetworkIds));
        $this->newLine();

        // All network IDs
        $allNetworkIds = DB::table('networks')->pluck('network_id')->toArray();
        $this->info("All network IDs in system: " . count($allNetworkIds));
        $this->line(json_encode($allNetworkIds));
        $this->newLine();

        // Unmapped networks
        $unmappedNetworkIds = array_diff($allNetworkIds, $assignedNetworkIds);
        $this->warn("Unmapped network IDs: " . count($unmappedNetworkIds));
        
        if (!empty($unmappedNetworkIds)) {
            $unmapped = DB::table('networks')
                ->whereIn('network_id', $unmappedNetworkIds)
                ->get(['network_id', 'subnet']);
            
            $this->table(['Network ID', 'Subnet'], $unmapped->map(fn($n) => [$n->network_id, $n->subnet]));
        } else {
            $this->info('✓ All networks are assigned to buildings!');
        }

        $this->newLine();
        $this->info('=== CONTROLLER QUERY SIMULATION ===');
        $this->newLine();

        // Simulate the controller query
        $unmappedNetworks = DB::table('networks as n')
            ->leftJoin('devices as d', 'd.network_id', '=', 'n.network_id')
            ->whereNotIn('n.network_id', $assignedNetworkIds)
            ->groupBy('n.network_id', 'n.subnet')
            ->selectRaw('n.network_id, n.subnet, COUNT(DISTINCT d.device_id) as total_devices')
            ->get();

        $this->info("Unmapped networks from query: " . $unmappedNetworks->count());
        
        if ($unmappedNetworks->count() > 0) {
            $this->table(
                ['Network ID', 'Subnet', 'Total Devices'],
                $unmappedNetworks->map(fn($n) => [$n->network_id, $n->subnet, $n->total_devices])
            );
        }

        return 0;
    }
}
