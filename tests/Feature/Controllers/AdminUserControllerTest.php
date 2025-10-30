<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeUser(string $role = 'user'): User {
    return User::factory()->create(['role' => $role]);
}

test('admin users index is accessible by admin roles and denied for normal users', function () {
    $admin = makeUser('admin');
    $res = $this->actingAs($admin)->get(route('admin.users.index'));
    $res->assertOk();

    $user = makeUser('user');
    $this->actingAs($user)->get(route('admin.users.index'))->assertNotFound(); // AdminOnly aborts 404
});

test('super admin can create admin; admin cannot create admin', function () {
    $super = makeUser('super_admin');

    // super_admin creates an admin
    $payload = [
        'name' => 'Alice Admin',
        'email' => 'alice@example.com',
        'password' => 'password123',
        'role' => 'admin',
    ];
    $this->actingAs($super)->post(route('admin.users.store'), $payload)
        ->assertRedirect(); // back with status
    $this->assertDatabaseHas('users', ['email' => 'alice@example.com', 'role' => 'admin']);

    // admin cannot create another admin
    $admin = makeUser('admin');
    $payload2 = [
        'name' => 'Bob Admin',
        'email' => 'bob@example.com',
        'password' => 'password123',
        'role' => 'admin',
    ];
    $this->actingAs($admin)->post(route('admin.users.store'), $payload2)
        ->assertSessionHasErrors('role');
    $this->assertDatabaseMissing('users', ['email' => 'bob@example.com']);
});

test('super admin cannot create a second super admin', function () {
    $super = makeUser('super_admin');

    $this->actingAs($super)->post(route('admin.users.store'), [
        'name' => 'Another Super',
        'email' => 'super2@example.com',
        'password' => 'password123',
        'role' => 'super_admin',
    ])->assertSessionHasErrors('role');

    $this->assertDatabaseMissing('users', ['email' => 'super2@example.com']);
});

test('only super admin can update roles; cannot modify own role or a super admin', function () {
    $super = makeUser('super_admin');
    $user = makeUser('user');

    // super admin promotes a user to admin
    $this->actingAs($super)->patch(route('admin.users.updateRole', $user), [
        'role' => 'admin',
    ])->assertRedirect();
    $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'admin']);

    // super admin cannot change own role
    $this->actingAs($super)->patch(route('admin.users.updateRole', $super), [
        'role' => 'user',
    ])->assertSessionHasErrors('role');

    // cannot modify a super_admin's role
    $anotherSuper = makeUser('super_admin');
    $this->actingAs($super)->patch(route('admin.users.updateRole', $anotherSuper), [
        'role' => 'admin',
    ])->assertSessionHasErrors('role');

    // admin cannot update roles (403)
    $admin = makeUser('admin');
    $target = makeUser('user');
    $this->actingAs($admin)->patch(route('admin.users.updateRole', $target), [
        'role' => 'admin',
    ])->assertForbidden();
});

test('delete policies: super admin can delete users; admin cannot delete admin; cannot delete super admin or self', function () {
    $super = makeUser('super_admin');
    $user = makeUser('user');

    // super admin deletes a normal user
    $this->actingAs($super)->delete(route('admin.users.destroy', $user))
        ->assertRedirect();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);

    // admin cannot delete another admin
    $admin1 = makeUser('admin');
    $admin2 = makeUser('admin');
    $this->actingAs($admin1)->delete(route('admin.users.destroy', $admin2))
        ->assertSessionHasErrors('delete');
    $this->assertDatabaseHas('users', ['id' => $admin2->id]);

    // cannot delete super admin
    $super2 = makeUser('super_admin');
    $this->actingAs($super)->delete(route('admin.users.destroy', $super2))
        ->assertSessionHasErrors('delete');
    $this->assertDatabaseHas('users', ['id' => $super2->id]);

    // cannot delete self
    $this->actingAs($super)->delete(route('admin.users.destroy', $super))
        ->assertSessionHasErrors('delete');
    $this->assertDatabaseHas('users', ['id' => $super->id]);
});
