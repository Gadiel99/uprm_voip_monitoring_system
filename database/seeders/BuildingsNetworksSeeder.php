<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Buildings;
use App\Models\Networks;

class BuildingsNetworksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cti = Buildings::create([
            'name' => 'CTI',
        ]);

        $network1 = Networks::create([
            'subnet'=>'10.100.0.0/24',
            'offline_devices'=>0,
            'total_devices'=>0,
        ]);

        $network2 = Networks::create([
            'subnet'=>'10.100.147.0/24',
            'offline_devices'=>0,
            'total_devices'=>0,
        ]);

        $cti->networks()->attach([
            $network1->network_id, 
            $network2->network_id
        ]);

        
        $this->command->info('✅ Created Building: ' . $cti->name);
        $this->command->info('✅ Created Network: ' . $network1->subnet . ' (network_id: ' . $network1->network_id . ')');
        $this->command->info('✅ Created Network: ' . $network2->subnet . ' (network_id: ' . $network2->network_id . ')');
        $this->command->info('✅ Attached networks to building');
        
        $this->command->newLine();
        $this->command->info('📊 Summary:');
        $this->command->info('   Buildings: ' . Buildings::count());
        $this->command->info('   Networks: ' . Networks::count());
    }
}
