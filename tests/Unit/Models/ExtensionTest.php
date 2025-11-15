<?php

use App\Models\Extensions;
use App\Models\Devices;
use App\Models\Network;

test('extension can be created with valid data', function () {
    $extension = Extensions::create([
        'extension_number' => '5001',
        'user_first_name' => 'Alice',
        'user_last_name' => 'Johnson',
    ]);

    expect($extension)->toBeInstanceOf(Extensions::class)
        ->and($extension->extension_number)->toBe('5001')
        ->and($extension->user_first_name)->toBe('Alice')
        ->and($extension->user_last_name)->toBe('Johnson')
        ->and($extension->extension_id)->not->toBeNull();
});

test('extension has many devices relationship', function () {
    $network = Network::create([
        'subnet' => '10.100.0.0/24',
        'offline_devices' => 0,
        'total_devices' => 0,
    ]);

    $device1 = Devices::create([
        'ip_address' => '10.100.0.100',
        'network_id' => $network->network_id,
        'status' => 'online',
    ]);

    $device2 = Devices::create([
        'ip_address' => '10.100.0.101',
        'network_id' => $network->network_id,
        'status' => 'online',
    ]);

    $extension = Extensions::create([
        'extension_number' => '2001',
        'user_first_name' => 'Bob',
        'user_last_name' => 'Williams',
    ]);

    $extension->devices()->attach([$device1->device_id, $device2->device_id]);

    expect($extension->devices)->toHaveCount(2)
        ->and($extension->devices->first())->toBeInstanceOf(Devices::class);
});

test('extension number is unique', function () {
    Extensions::create([
        'extension_number' => '3001',
        'user_first_name' => 'Charlie',
        'user_last_name' => 'Brown',
    ]);

    $this->expectException(\Illuminate\Database\QueryException::class);

    Extensions::create([
        'extension_number' => '3001',
        'user_first_name' => 'David',
        'user_last_name' => 'Miller',
    ]);
});

test('extension can be updated', function () {
    $extension = Extensions::create([
        'extension_number' => '4001',
        'user_first_name' => 'Old',
        'user_last_name' => 'Name',
    ]);

    $extension->update([
        'user_first_name' => 'New',
        'user_last_name' => 'User',
    ]);

    expect($extension->fresh()->user_first_name)->toBe('New')
        ->and($extension->fresh()->user_last_name)->toBe('User');
});

test('multiple extensions can exist', function () {
    Extensions::create(['extension_number' => '6001', 'user_first_name' => 'User', 'user_last_name' => 'One']);
    Extensions::create(['extension_number' => '6002', 'user_first_name' => 'User', 'user_last_name' => 'Two']);
    Extensions::create(['extension_number' => '6003', 'user_first_name' => 'User', 'user_last_name' => 'Three']);

    expect(Extensions::count())->toBe(3);
});

test('extension can be deleted', function () {
    $extension = Extensions::create([
        'extension_number' => '7001',
        'user_first_name' => 'Delete',
        'user_last_name' => 'Me',
    ]);

    $extensionId = $extension->extension_id;
    $extension->delete();

    expect(Extensions::find($extensionId))->toBeNull();
});

test('extension full name can be retrieved', function () {
    $extension = Extensions::create([
        'extension_number' => '8001',
        'user_first_name' => 'Test',
        'user_last_name' => 'User',
    ]);

    $fullName = $extension->user_first_name . ' ' . $extension->user_last_name;

    expect($fullName)->toBe('Test User');
});
