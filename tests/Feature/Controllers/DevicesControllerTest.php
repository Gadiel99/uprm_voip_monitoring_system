<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('devices index requires schema; skipped if tables missing', function () {
    $user = User::factory()->create();

    if (! Schema::hasTable('buildings')) {
        $this->markTestSkipped('Esquema de buildings/networks/devices no existe en pruebas.');
    }

    $this->actingAs($user)->get('/devices')->assertOk();
});

test('devices by building requires schema; skipped if tables missing', function () {
    $user = User::factory()->create();

    if (! Schema::hasTable('buildings')) {
        $this->markTestSkipped('Esquema de buildings/networks/devices no existe en pruebas.');
    }

    // Ajusta con un ID válido si usas seeders para buildings
    $this->actingAs($user)->get('/devices/building/1')->assertStatus(404);
});
