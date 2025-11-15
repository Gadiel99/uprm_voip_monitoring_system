<?php

use Illuminate\Support\Facades\Schema;

test('buildings table exists', function () {
    expect(Schema::hasTable('buildings'))->toBeTrue();
});

test('buildings table has correct columns', function () {
    expect(Schema::hasColumn('buildings', 'building_id'))->toBeTrue()
        ->and(Schema::hasColumn('buildings', 'name'))->toBeTrue()
        ->and(Schema::hasColumn('buildings', 'created_at'))->toBeTrue()
        ->and(Schema::hasColumn('buildings', 'updated_at'))->toBeTrue();
});

test('devices table exists', function () {
    expect(Schema::hasTable('devices'))->toBeTrue();
});

test('devices table has correct columns', function () {
    expect(Schema::hasColumn('devices', 'device_id'))->toBeTrue()
        ->and(Schema::hasColumn('devices', 'ip_address'))->toBeTrue()
        ->and(Schema::hasColumn('devices', 'network_id'))->toBeTrue()
        ->and(Schema::hasColumn('devices', 'status'))->toBeTrue()
        ->and(Schema::hasColumn('devices', 'created_at'))->toBeTrue()
        ->and(Schema::hasColumn('devices', 'updated_at'))->toBeTrue();
});

test('extensions table exists', function () {
    expect(Schema::hasTable('extensions'))->toBeTrue();
});

test('extensions table has correct columns', function () {
    expect(Schema::hasColumn('extensions', 'extension_id'))->toBeTrue()
        ->and(Schema::hasColumn('extensions', 'extension_number'))->toBeTrue()
        ->and(Schema::hasColumn('extensions', 'user_first_name'))->toBeTrue()
        ->and(Schema::hasColumn('extensions', 'user_last_name'))->toBeTrue()
        ->and(Schema::hasColumn('extensions', 'created_at'))->toBeTrue()
        ->and(Schema::hasColumn('extensions', 'updated_at'))->toBeTrue();
});

test('networks table exists', function () {
    expect(Schema::hasTable('networks'))->toBeTrue();
});

test('networks table has correct columns', function () {
    expect(Schema::hasColumn('networks', 'network_id'))->toBeTrue()
        ->and(Schema::hasColumn('networks', 'subnet'))->toBeTrue()
        ->and(Schema::hasColumn('networks', 'offline_devices'))->toBeTrue()
        ->and(Schema::hasColumn('networks', 'total_devices'))->toBeTrue()
        ->and(Schema::hasColumn('networks', 'created_at'))->toBeTrue()
        ->and(Schema::hasColumn('networks', 'updated_at'))->toBeTrue();
});

test('building_networks pivot table exists', function () {
    expect(Schema::hasTable('building_networks'))->toBeTrue();
});

test('building_networks pivot table has correct columns', function () {
    expect(Schema::hasColumn('building_networks', 'building_id'))->toBeTrue()
        ->and(Schema::hasColumn('building_networks', 'network_id'))->toBeTrue();
});

test('device_extensions pivot table exists', function () {
    expect(Schema::hasTable('device_extensions'))->toBeTrue();
});

test('device_extensions pivot table has correct columns', function () {
    expect(Schema::hasColumn('device_extensions', 'device_id'))->toBeTrue()
        ->and(Schema::hasColumn('device_extensions', 'extension_id'))->toBeTrue()
        ->and(Schema::hasColumn('device_extensions', 'created_at'))->toBeTrue()
        ->and(Schema::hasColumn('device_extensions', 'updated_at'))->toBeTrue();
});
