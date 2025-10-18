<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('success flash messages are shared with inertia', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['success' => 'Operación exitosa'])
        ->get('/dashboard');

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->has('flash.success')
            ->where('flash.success', 'Operación exitosa')
    );
});

test('error flash messages are shared with inertia', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['error' => 'Ocurrió un error'])
        ->get('/dashboard');

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->has('flash.error')
            ->where('flash.error', 'Ocurrió un error')
    );
});

test('warning flash messages are shared with inertia', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['warning' => 'Advertencia importante'])
        ->get('/dashboard');

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->has('flash.warning')
            ->where('flash.warning', 'Advertencia importante')
    );
});

test('info flash messages are shared with inertia', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['info' => 'Información relevante'])
        ->get('/dashboard');

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->has('flash.info')
            ->where('flash.info', 'Información relevante')
    );
});

test('status flash messages are shared with inertia', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['status' => 'verification-link-sent'])
        ->get('/dashboard');

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->has('flash.status')
            ->where('flash.status', 'verification-link-sent')
    );
});

test('message object flash messages are shared with inertia', function () {
    $user = User::factory()->create();

    $message = [
        'title' => 'Usuario creado',
        'description' => 'El usuario ha sido creado correctamente.',
    ];

    $response = $this
        ->actingAs($user)
        ->withSession(['message' => $message])
        ->get('/dashboard');

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->has('flash.message')
            ->where('flash.message.title', 'Usuario creado')
            ->where('flash.message.description', 'El usuario ha sido creado correctamente.')
    );
});

test('multiple flash messages can be shared simultaneously', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession([
            'success' => 'Operación exitosa',
            'info' => 'Información adicional',
        ])
        ->get('/dashboard');

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->has('flash.success')
            ->has('flash.info')
            ->where('flash.success', 'Operación exitosa')
            ->where('flash.info', 'Información adicional')
    );
});
