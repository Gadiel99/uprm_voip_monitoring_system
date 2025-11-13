<?php

use App\Models\Building;
use App\Models\Network;

test('building can be created with valid data', function () {
    $building = Building::create([
        'name' => 'Test Building',
    ]);

    expect($building)->toBeInstanceOf(Building::class)
        ->and($building->name)->toBe('Test Building')
        ->and($building->building_id)->not->toBeNull();
});

test('building has many networks relationship', function () {
    $building = Building::create(['name' => 'CTI']);
    
    $network1 = Network::create([
        'subnet' => '10.100.0.0/24',
        'offline_devices' => 0,
        'total_devices' => 0,
    ]);
    
    $network2 = Network::create([
        'subnet' => '10.100.147.0/24',
        'offline_devices' => 0,
        'total_devices' => 0,
    ]);

    $building->networks()->attach([$network1->network_id, $network2->network_id]);

    expect($building->networks)->toHaveCount(2)
        ->and($building->networks->first())->toBeInstanceOf(Network::class);
});

test('building can retrieve its networks', function () {
    $building = Building::create(['name' => 'Library']);
    
    $network = Network::create([
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
    
    Building::create([]);
});

test('multiple buildings can exist', function () {
    Building::create(['name' => 'Building A']);
    Building::create(['name' => 'Building B']);
    Building::create(['name' => 'Building C']);

    expect(Building::count())->toBe(3);
});

test('building can be updated', function () {
    $building = Building::create(['name' => 'Old Name']);
    
    $building->update(['name' => 'New Name']);

    expect($building->fresh()->name)->toBe('New Name');
});

test('building can be deleted', function () {
    $building = Building::create(['name' => 'To Delete']);
    $buildingId = $building->building_id;
    
    $building->delete();

    expect(Building::find($buildingId))->toBeNull();
});
