<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('blocks non-auth users from /admin', function() {
    $this->get('/admin')->assertStatus(302); // redirect to login
});

it('blocks normal users from /admin', function() {
    $user = User::factory()->create(['role' => 'user']);
    $this->actingAs($user);
    $this->get('/admin')->assertStatus(404); // AdminOnly returns 404
});

it('allows admins to access /admin', function() {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);
    $this->get('/admin')->assertStatus(200);
});
