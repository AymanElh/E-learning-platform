<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can register', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => "test user",
        'email' => "test@example.com",
        'password' => "12345678",
        'password_confirmation' => "12345678"
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in'
        ]);

    $this->assertDatabaseHas('users', [
        'name' => 'test user',
        'email' => 'test@example.com',
    ]);
});


test('user can login', function() {
    User::factory()->create([
        'name' => "Test user",
        'email' => "test@example.com",
        'password' => "12345678"
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => "test@example.com",
        'password' => "12345678"
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in'
        ]);
});


test('user can logout', function() {
    $user = User::factory()->create();
    $token = auth()->login($user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token
    ])->postJson('/api/v1/logout');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Logout successfully'
        ]);
});
