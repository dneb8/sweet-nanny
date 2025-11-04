<?php

use App\Enums\User\RoleEnum;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    // sembramos los roles de Spatie primero
    $this->seed(RoleSeeder::class);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
    ]);

    // obtenemos al usuario recién creado
    $user = User::where('email', 'testuser@example.com')->first();

    // le asignamos el rol de tutor con Spatie
    $user->assignRole(RoleEnum::TUTOR->value);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('verification email is sent on registration', function () {
    Notification::fake();

    // sembramos los roles de Spatie primero
    $this->seed(RoleSeeder::class);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
    ]);

    $user = User::where('email', 'testuser@example.com')->first();

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('tutor instance is created on registration', function () {
    // sembramos los roles de Spatie primero
    $this->seed(RoleSeeder::class);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
    ]);

    // obtenemos al usuario recién creado
    $user = User::where('email', 'testuser@example.com')->first();

    // verificamos que el usuario tiene el rol de tutor
    expect($user->hasRole(RoleEnum::TUTOR->value))->toBeTrue();

    // verificamos que el usuario tiene una instancia de Tutor asociada
    expect($user->tutor)->not->toBeNull();
    expect($user->tutor)->toBeInstanceOf(\App\Models\Tutor::class);

    $response->assertRedirect(route('dashboard', absolute: false));
});
