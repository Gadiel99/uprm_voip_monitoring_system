<?php

use App\Models\Devices;
use App\Models\Network;
use App\Models\Extensions;

test('device can be created with valid data', function () {
    $network = Network::create([
        'subnet' => '10.100.0.0/24',
        'offline_devices' => 0,
        'total_devices' => 0,
    ]);

    $device = Devices::create([
        'ip_address' => '10.100.0.50',
        'network_id' => $network->network_id,
        'status' => 'online',
    ]);

    expect($device)->toBeInstanceOf(Devices::class)
        ->and($device->ip_address)->toBe('10.100.0.50')
        ->and($device->status)->toBe('online')
        ->and($device->device_id)->not->toBeNull();
});

test('device belongs to a network', function () {
    $network = Network::create([
        'subnet' => '192.168.1.0/24',
        'offline_devices' => 0,
        'total_devices' => 5,
    ]);

    $device = Devices::create([
        'ip_address' => '192.168.1.100',
        'network_id' => $network->network_id,
        'status' => 'online',
    ]);

    expect($device->network)->toBeInstanceOf(Network::class)
        ->and($device->network->subnet)->toBe('192.168.1.0/24');
});

test('device has many extensions relationship', function () {
    $network = Network::create([
        'subnet' => '10.100.0.0/24',
        'offline_devices' => 0,
        'total_devices' => 0,
    ]);

    $device = Devices::create([
        'ip_address' => '10.100.0.25',
        'network_id' => $network->network_id,
        'status' => 'online',
    ]);

    $extension1 = Extensions::create([
        'extension_number' => '1001',
        'user_first_name' => 'John',
        'user_last_name' => 'Doe',
    ]);

    $extension2 = Extensions::create([
        'extension_number' => '1002',
        'user_first_name' => 'Jane',
        'user_last_name' => 'Smith',
    ]);

    $device->extensions()->attach([$extension1->extension_id, $extension2->extension_id]);

    expect($device->extensions)->toHaveCount(2)
        ->and($device->extensions->first())->toBeInstanceOf(Extensions::class);
});

test('device status can be online or offline', function () {
    $network = Network::create([
        'subnet' => '10.100.0.0/24',
        'offline_devices' => 0,
        'total_devices' => 0,
    ]);

    $deviceOnline = Devices::create([
        'ip_address' => '10.100.0.10',
        'network_id' => $network->network_id,
        'status' => 'online',
    ]);

    $deviceOffline = Devices::create([
        'ip_address' => '10.100.0.11',
        'network_id' => $network->network_id,
        'status' => 'offline',
    ]);

    expect($deviceOnline->status)->toBe('online')
        ->and($deviceOffline->status)->toBe('offline');
});

test('device can be updated', function () {
    $network = Network::create([
        'subnet' => '10.100.0.0/24',
        'offline_devices' => 0,
        'total_devices' => 0,
    ]);

    $device = Devices::create([
        'ip_address' => '10.100.0.20',
        'network_id' => $network->network_id,
        'status' => 'online',
    ]);

    $device->update(['status' => 'offline']);

    expect($device->fresh()->status)->toBe('offline');
});

test('device ip address is unique', function () {
    $network = Network::create([
        'subnet' => '10.100.0.0/24',
        'offline_devices' => 0,
        'total_devices' => 0,
    ]);

    Devices::create([
        'ip_address' => '10.100.0.30',
        'network_id' => $network->network_id,
        'status' => 'online',
    ]);

    $this->expectException(\Illuminate\Database\QueryException::class);

    Devices::create([
        'ip_address' => '10.100.0.30',
        'network_id' => $network->network_id,
        'status' => 'online',
    ]);
});

test('device can be deleted', function () {
    $network = Network::create([
        'subnet' => '10.100.0.0/24',
        'offline_devices' => 0,
        'total_devices' => 0,
    ]);

    $device = Devices::create([
        'ip_address' => '10.100.0.40',
        'network_id' => $network->network_id,
        'status' => 'online',
    ]);

    $deviceId = $device->device_id;
    $device->delete();

    expect(Devices::find($deviceId))->toBeNull();
});
