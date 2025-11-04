<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;

test('newly registered tutor can create a child', function () {
    // sembramos los roles de Spatie primero
    $this->seed(RoleSeeder::class);

    // registramos un nuevo usuario
    $response = $this->post('/register', [
        'name' => 'Test Tutor',
        'email' => 'tutor@example.com',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
    ]);

    // obtenemos al usuario recién creado
    $user = User::where('email', 'tutor@example.com')->first();

    // marcamos el email como verificado
    $user->markEmailAsVerified();

    // verificamos que el usuario tiene una instancia de Tutor asociada
    expect($user->tutor)->not->toBeNull();

    // intentamos crear un hijo usando el tutor_id
    $childResponse = $this->actingAs($user)->postJson('/children', [
        'tutor_id' => $user->tutor->id,
        'name' => 'Test Child',
        'birthdate' => '2020-01-01',
        'kinkship' => 'hijo',
    ]);

    // verificamos que la creación fue exitosa
    $childResponse->assertStatus(201);
    $childResponse->assertJson([
        'name' => 'Test Child',
        'kinkship' => 'hijo',
    ]);

    // verificamos que el hijo está en la base de datos
    $this->assertDatabaseHas('children', [
        'tutor_id' => $user->tutor->id,
        'name' => 'Test Child',
        'kinkship' => 'hijo',
    ]);
});
