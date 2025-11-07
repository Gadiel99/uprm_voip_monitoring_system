<?php

use App\Models\Buildings;
use App\Models\Networks;

test('building can be created with valid data', function () {
    $building = Buildings::create([
        'name' => 'Test Building',
    ]);

    expect($building)->toBeInstanceOf(Buildings::class)
        ->and($building->name)->toBe('Test Building')
        ->and($building->building_id)->not->toBeNull();
});

test('building has many networks relationship', function () {
    $building = Buildings::create(['name' => 'CTI']);
    
    $network1 = Networks::create([
        'subnet' => '10.100.0.0/24',
        'offline_devices' => 0,
        'total_devices' => 0,
    ]);
    
    $network2 = Networks::create([
        'subnet' => '10.100.147.0/24',
        'offline_devices' => 0,
        'total_devices' => 0,
    ]);

    $building->networks()->attach([$network1->network_id, $network2->network_id]);

    expect($building->networks)->toHaveCount(2)
        ->and($building->networks->first())->toBeInstanceOf(Networks::class);
});

test('building can retrieve its networks', function () {
    $building = Buildings::create(['name' => 'Library']);
    
    $network = Networks::create([
        'subnet' => '192.168.1.0/24',
        'offline_devices' => 0,
        'total_devices' => 10,
    ]);

    $building->networks()->attach($network->network_id);

    $retrievedNetworks = $building->networks;
    
    expect($retrievedNetworks)->toHaveCount(1)
        ->and($retrievedNetworks->first()->subnet)->toBe('192.168.1.0/24');
});

test('building name is required', function () {
    $this->expectException(\Illuminate\Database\QueryException::class);
    
    Buildings::create([]);
});

test('multiple buildings can exist', function () {
    Buildings::create(['name' => 'Building A']);
    Buildings::create(['name' => 'Building B']);
    Buildings::create(['name' => 'Building C']);

    expect(Buildings::count())->toBe(3);
});

test('building can be updated', function () {
    $building = Buildings::create(['name' => 'Old Name']);
    
    $building->update(['name' => 'New Name']);

    expect($building->fresh()->name)->toBe('New Name');
});

test('building can be deleted', function () {
    $building = Buildings::create(['name' => 'To Delete']);
    $buildingId = $building->building_id;
    
    $building->delete();

    expect(Buildings::find($buildingId))->toBeNull();
});
