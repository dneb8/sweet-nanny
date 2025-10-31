<?php

use App\Enums\User\RoleEnum;
use App\Models\User;
use Database\Seeders\RoleSeeder;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->seed(RoleSeeder::class);
    $user->assignRole(RoleEnum::TUTOR->value); // Asignar rol con Spatie

    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});
