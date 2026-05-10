<?php

use App\Models\User;

test('user can register and receive token', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Temur',
        'email' => 'temur@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.user.email', 'temur@example.com')
        ->assertJsonStructure(['data' => ['token']]);
});

test('user can login and receive token', function () {
    $user = User::factory()->create([
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.user.email', $user->email)
        ->assertJsonStructure(['data' => ['token']]);
});

test('user cannot login with invalid credentials', function () {
    $user = User::factory()->create([
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertUnprocessable();
});

test('guest cannot access protected image routes', function () {
    $this->getJson('/api/images')->assertUnauthorized();
    $this->postJson('/api/logout')->assertUnauthorized();
});
