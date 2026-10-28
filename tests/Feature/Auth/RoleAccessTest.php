<?php

use App\Models\User;

test('guest is redirected to login when accessing admin dashboard', function () {
    $this->get('/admin/dashboard')->assertRedirect('/login');
});

test('regular user cannot access admin dashboard', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->get('/admin/dashboard')
        ->assertForbidden();
});

test('admin can access admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertOk();
});

test('admin can access admin dashboard after session login', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();

    $this->get('/admin/dashboard')->assertOk();
});

test('authenticated user can access user dashboard', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->get('/user/dashboard')
        ->assertOk();
});

test('admin can list users', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertOk();
});

test('admin can change another user role', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($admin)
        ->patch("/admin/users/{$user->id}/role", ['role' => 'admin'])
        ->assertRedirect();

    expect($user->fresh()->role)->toBe('admin');
});

test('admin cannot change their own role', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->patch("/admin/users/{$admin->id}/role", ['role' => 'user'])
        ->assertRedirect();

    expect($admin->fresh()->role)->toBe('admin');
});

test('admin can delete another user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($admin)
        ->delete("/admin/users/{$user->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
