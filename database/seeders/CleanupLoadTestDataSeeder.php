<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Devices;
use App\Models\Extensions;
use App\Models\Network;

class CleanupLoadTestDataSeeder extends Seeder
{
    /**
     * Remove all load test data while preserving real test data in Monzon and Prueba
     * Also preserves all critical devices marked from admin panel
     */
    public function run()
    {
        $criticalCount = Devices::where('is_critical', 1)->count();
        
        $this->command->warn('⚠️  WARNING: This will remove all LoadTest data!');
        $this->command->warn('⚠️  Preserving: Monzon (building_id: 99), Prueba (building_id: 211), and {$criticalCount} critical devices');
        
        if (!$this->command->confirm('Do you want to continue?', false)) {
            $this->command->info('Cleanup cancelled.');
            return;
        }
        
        $this->command->info('🧹 Starting cleanup...');
        $startTime = microtime(true);
        
        // Get LoadTest extension IDs
        $loadTestExtensionIds = Extensions::where('user_first_name', 'LoadTest')->pluck('extension_id');
        
        if ($loadTestExtensionIds->isEmpty()) {
            $this->command->info('ℹ️  No LoadTest data found.');
            return;
        }
        
        $this->command->info("📊 Found {$loadTestExtensionIds->count()} LoadTest extensions");
        
        // Get device IDs associated with LoadTest extensions
        $loadTestDeviceIds = DB::table('device_extensions')
            ->whereIn('extension_id', $loadTestExtensionIds)
            ->pluck('device_id');
        
        // Double-check: Remove any critical device IDs from deletion list (safety measure)
        $criticalDeviceIds = Devices::where('is_critical', 1)->pluck('device_id');
        $loadTestDeviceIds = $loadTestDeviceIds->diff($criticalDeviceIds);
        
        if ($criticalDeviceIds->isNotEmpty()) {
            $this->command->info("🔒 Protected {$criticalDeviceIds->count()} critical devices from deletion");
        }
        
        $this->command->info("📱 Found {$loadTestDeviceIds->count()} LoadTest devices");
        
        // Delete in correct order to maintain referential integrity
        
        // 1. Delete device_extensions relationships
        $deletedRelations = DB::table('device_extensions')
            ->whereIn('extension_id', $loadTestExtensionIds)
            ->delete();
        $this->command->info("✓ Deleted {$deletedRelations} device-extension relationships");
        
        // 2. Delete devices
        $deletedDevices = Devices::whereIn('device_id', $loadTestDeviceIds)->delete();
        $this->command->info("✓ Deleted {$deletedDevices} devices");
        
        // 3. Delete extensions
        $deletedExtensions = Extensions::where('user_first_name', 'LoadTest')->delete();
        $this->command->info("✓ Deleted {$deletedExtensions} extensions");
        
        // 4. Update network device counts
        $this->command->info('🔄 Updating network device counts...');
        $networks = Network::all();
        foreach ($networks as $network) {
            $network->updateDeviceCounts();
        }
        $this->command->info("✓ Updated {$networks->count()} networks");
        
        $executionTime = round(microtime(true) - $startTime, 2);
        
        $this->command->info("\n" . str_repeat('=', 60));
        $this->command->info("✅ Cleanup Complete!");
        $this->command->info(str_repeat('=', 60));
        $this->command->info("⏱️  Execution Time: {$executionTime}s");
        
        // Show final statistics
        $totalDevices = Devices::count();
        $totalExtensions = Extensions::count();
        
        $this->command->info("\n📈 Remaining Data:");
        $this->command->info("  - Devices: {$totalDevices}");
        $this->command->info("  - Extensions: {$totalExtensions}");
        $this->command->info(str_repeat('=', 60) . "\n");
    }
}
