<?php

use App\Http\Controllers\AccountController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

test('account controller can update settings directly', function () {
    $user = User::factory()->create(['email' => 'old@example.com']);
    $this->actingAs($user);

    $controller = app(AccountController::class);

    $request = Request::create('/account/settings', 'POST', [
        'name' => 'Updated User',
        'email' => 'updated@example.com',
        'password' => '',
        'password_confirmation' => '',
    ], [], [], ['HTTP_REFERER' => '/']);

    $response = $controller->updateSettings($request);

    expect($response->isRedirect())->toBeTrue();
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated User',
        'email' => 'updated@example.com',
    ]);
});
