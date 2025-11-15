<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin dashboard route is reachable by admin', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/admin');

    // Acepta 200 (render) o 302 (redirect a admin.users.index)
    expect(in_array($response->getStatusCode(), [200, 302]))->toBeTrue();
});

test('admin dashboard route is not reachable by normal user', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)->get('/admin')->assertNotFound(); // AdminOnly aborts 404
});
