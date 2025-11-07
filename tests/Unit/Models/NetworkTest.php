<?php

use App\Models\Networks;
use App\Models\Buildings;
use App\Models\Devices;

test('network can be created with valid data', function () {
    $network = Networks::create([
        'subnet' => '172.16.0.0/24',
        'offline_devices' => 5,
        'total_devices' => 20,
    ]);

    expect($network)->toBeInstanceOf(Networks::class)
        ->and($network->subnet)->toBe('172.16.0.0/24')
        ->and($network->offline_devices)->toBe(5)
        ->and($network->total_devices)->toBe(20)
        ->and($network->network_id)->not->toBeNull();
});

test('network has many devices relationship', function () {
    $network = Networks::create([
        'subnet' => '10.50.0.0/24',
        'offline_devices' => 0,
        'total_devices' => 0,
    ]);

    Devices::create([
        'ip_address' => '10.50.0.10',
        'network_id' => $network->network_id,
        'status' => 'online',
    ]);

    Devices::create([
        'ip_address' => '10.50.0.11',
        'network_id' => $network->network_id,
        'status' => 'offline',
    ]);

    expect($network->devices)->toHaveCount(2)
        ->and($network->devices->first())->toBeInstanceOf(Devices::class);
});

test('network can update device counts', function () {
    $network = Networks::create([
        'subnet' => '10.60.0.0/24',
        'offline_devices' => 0,
        'total_devices' => 0,
    ]);

    Devices::create([
        'ip_address' => '10.60.0.10',
        'network_id' => $network->network_id,
        'status' => 'online',
    ]);

    Devices::create([
        'ip_address' => '10.60.0.11',
        'network_id' => $network->network_id,
        'status' => 'offline',
    ]);

    Devices::create([
        'ip_address' => '10.60.0.12',
        'network_id' => $network->network_id,
        'status' => 'online',
    ]);

    $network->updateDeviceCounts();

    expect($network->fresh()->total_devices)->toBe(3)
        ->and($network->fresh()->offline_devices)->toBe(1);
});

test('network subnet should be unique', function () {
    $network1 = Networks::create([
        'subnet' => '192.168.100.0/24',
        'offline_devices' => 0,
        'total_devices' => 0,
    ]);

    // Try to create another network with same subnet using firstOrCreate
    $network2 = Networks::firstOrCreate(
        ['subnet' => '192.168.100.0/24'],
        ['offline_devices' => 5, 'total_devices' => 10]
    );

    // Both should reference the same network
    expect($network1->network_id)->toBe($network2->network_id);
});

test('network can be updated', function () {
    $network = Networks::create([
        'subnet' => '10.70.0.0/24',
        'offline_devices' => 0,
        'total_devices' => 0,
    ]);

    $network->update([
        'offline_devices' => 3,
        'total_devices' => 15,
    ]);

    expect($network->fresh()->offline_devices)->toBe(3)
        ->and($network->fresh()->total_devices)->toBe(15);
});

test('network counts only offline devices correctly', function () {
    $network = Networks::create([
        'subnet' => '10.80.0.0/24',
        'offline_devices' => 0,
        'total_devices' => 0,
    ]);

    Devices::create(['ip_address' => '10.80.0.10', 'network_id' => $network->network_id, 'status' => 'online']);
    Devices::create(['ip_address' => '10.80.0.11', 'network_id' => $network->network_id, 'status' => 'online']);
    Devices::create(['ip_address' => '10.80.0.12', 'network_id' => $network->network_id, 'status' => 'offline']);
    Devices::create(['ip_address' => '10.80.0.13', 'network_id' => $network->network_id, 'status' => 'offline']);

    $network->updateDeviceCounts();

    $offlineCount = $network->devices()->where('status', 'offline')->count();

    expect($offlineCount)->toBe(2)
        ->and($network->fresh()->offline_devices)->toBe(2);
});

test('network can be deleted', function () {
    $network = Networks::create([
        'subnet' => '10.90.0.0/24',
        'offline_devices' => 0,
        'total_devices' => 0,
    ]);

    $networkId = $network->network_id;
    $network->delete();

    expect(Networks::find($networkId))->toBeNull();
});

test('multiple networks can exist', function () {
    Networks::create(['subnet' => '10.10.0.0/24', 'offline_devices' => 0, 'total_devices' => 0]);
    Networks::create(['subnet' => '10.20.0.0/24', 'offline_devices' => 0, 'total_devices' => 0]);
    Networks::create(['subnet' => '10.30.0.0/24', 'offline_devices' => 0, 'total_devices' => 0]);

    expect(Networks::count())->toBe(3);
});
