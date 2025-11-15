<?php

use App\Models\Building;
use App\Models\Network;
use Database\Seeders\BuildingNetworksSeeder;

test('buildings networks seeder creates building successfully', function () {
    $seeder = new BuildingNetworksSeeder();
    $seeder->run();

    expect(Building::count())->toBeGreaterThan(0);
});

test('buildings networks seeder creates networks successfully', function () {
    $seeder = new BuildingNetworksSeeder();
    $seeder->run();

    expect(Network::count())->toBeGreaterThan(0);
});

test('buildings networks seeder attaches networks to building', function () {
    $seeder = new BuildingNetworksSeeder();
    $seeder->run();

    // Stefani building has multiple networks
    $building = Building::where('name', 'Stefani')->first();
    
    expect($building->networks->count())->toBeGreaterThan(0);
});

test('seeded building has correct name', function () {
    $seeder = new BuildingNetworksSeeder();
    $seeder->run();

    $building = Building::where('name', 'Stefani')->first();
    
    expect($building)->not->toBeNull()
        ->and($building->name)->toBe('Stefani');
});

test('seeded networks have correct subnets', function () {
    $seeder = new BuildingNetworksSeeder();
    $seeder->run();

    $network1 = Network::where('subnet', '10.100.124.0')->first();
    $network2 = Network::where('subnet', '10.100.56.0')->first();
    
    expect($network1)->not->toBeNull()
        ->and($network2)->not->toBeNull();
});

test('seeded networks have zero initial devices', function () {
    $seeder = new BuildingNetworksSeeder();
    $seeder->run();

    $networks = Network::all();
    
    foreach ($networks as $network) {
        expect($network->offline_devices)->toBe(0)
            ->and($network->total_devices)->toBe(0);
    }
});
