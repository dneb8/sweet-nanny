<?php

use App\Models\Address;
use App\Models\Tutor;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles if they don't exist
    Role::firstOrCreate(['name' => 'tutor', 'guard_name' => 'web']);
});

test('address can be created with coordinates', function () {
    $user = User::factory()->create();
    $tutor = Tutor::factory()->create(['user_id' => $user->id]);

    $addressData = [
        'postal_code' => '44100',
        'street' => 'Av. Vallarta 1234',
        'neighborhood' => 'Americana',
        'latitude' => 20.676176,
        'longitude' => -103.347890,
        'type' => 'casa',
        'addressable_type' => 'App\\Models\\Tutor',
        'addressable_id' => $tutor->id,
    ];

    $response = $this->actingAs($user)->post(route('addresses.store'), $addressData);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('addresses', [
        'postal_code' => '44100',
        'street' => 'Av. Vallarta 1234',
        'neighborhood' => 'Americana',
        'addressable_type' => 'App\\Models\\Tutor',
        'addressable_id' => $tutor->id,
    ]);

    $address = Address::where('addressable_id', $tutor->id)->first();
    expect($address->latitude)->toBe(20.676176);
    expect($address->longitude)->toBe(-103.347890);
});

test('address can be created without coordinates', function () {
    $user = User::factory()->create();
    $tutor = Tutor::factory()->create(['user_id' => $user->id]);

    $addressData = [
        'postal_code' => '44100',
        'street' => 'Av. Vallarta 1234',
        'neighborhood' => 'Americana',
        'type' => 'casa',
        'addressable_type' => 'App\\Models\\Tutor',
        'addressable_id' => $tutor->id,
    ];

    $response = $this->actingAs($user)->post(route('addresses.store'), $addressData);

    $response->assertSessionHas('success');

    $address = Address::where('addressable_id', $tutor->id)->first();
    expect($address->latitude)->toBeNull();
    expect($address->longitude)->toBeNull();
});

test('address coordinates are validated within valid ranges', function () {
    $user = User::factory()->create();
    $tutor = Tutor::factory()->create(['user_id' => $user->id]);

    $addressData = [
        'postal_code' => '44100',
        'street' => 'Av. Vallarta 1234',
        'neighborhood' => 'Americana',
        'latitude' => 91, // Invalid latitude
        'longitude' => -103.347890,
        'type' => 'casa',
        'addressable_type' => 'App\\Models\\Tutor',
        'addressable_id' => $tutor->id,
    ];

    $response = $this->actingAs($user)->post(route('addresses.store'), $addressData);

    $response->assertSessionHasErrors('latitude');
});
