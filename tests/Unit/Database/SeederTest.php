<?php

use App\Models\Buildings;
use App\Models\Networks;
use Database\Seeders\BuildingsNetworksSeeder;

test('buildings networks seeder creates building successfully', function () {
    $seeder = new BuildingsNetworksSeeder();
    $seeder->run();

    expect(Buildings::count())->toBeGreaterThan(0);
});

test('buildings networks seeder creates networks successfully', function () {
    $seeder = new BuildingsNetworksSeeder();
    $seeder->run();

    expect(Networks::count())->toBeGreaterThan(0);
});

test('buildings networks seeder attaches networks to building', function () {
    $seeder = new BuildingsNetworksSeeder();
    $seeder->run();

    $building = Buildings::first();
    
    expect($building->networks)->toHaveCount(2);
});

test('seeded building has correct name', function () {
    $seeder = new BuildingsNetworksSeeder();
    $seeder->run();

    $building = Buildings::where('name', 'CTI')->first();
    
    expect($building)->not->toBeNull()
        ->and($building->name)->toBe('CTI');
});

test('seeded networks have correct subnets', function () {
    $seeder = new BuildingsNetworksSeeder();
    $seeder->run();

    $network1 = Networks::where('subnet', '10.100.0.0/24')->first();
    $network2 = Networks::where('subnet', '10.100.147.0/24')->first();
    
    expect($network1)->not->toBeNull()
        ->and($network2)->not->toBeNull();
});

test('seeded networks have zero initial devices', function () {
    $seeder = new BuildingsNetworksSeeder();
    $seeder->run();

    $networks = Networks::all();
    
    foreach ($networks as $network) {
        expect($network->offline_devices)->toBe(0)
            ->and($network->total_devices)->toBe(0);
    }
});
