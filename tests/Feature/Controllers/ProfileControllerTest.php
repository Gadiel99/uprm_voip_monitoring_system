<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can update name and email', function () {
    $user = User::factory()->create(['email' => 'old@example.com']);
    $this->actingAs($user);

    $res = $this->patch(route('profile.update'), [
        'name' => 'New Name',
        'email' => 'new@example.com',
        'tab' => 'username',
    ]);

    $res->assertRedirect();
    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name', 'email' => 'new@example.com']);
});

test('user can update password with current_password rule', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $res = $this->patch(route('profile.password'), [
        'current_password' => 'password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $res->assertRedirect();
});

test('user can delete account with valid password', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $res = $this->delete(route('profile.destroy'), [
        'password' => 'password',
    ]);

    $res->assertRedirect('/');
    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
