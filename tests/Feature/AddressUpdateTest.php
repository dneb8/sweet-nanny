<?php

use App\Models\Address;
use App\Models\Tutor;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles if they don't exist
    Role::firstOrCreate(['name' => 'tutor', 'guard_name' => 'web']);
});

test('address can be updated with external_number', function () {
    $user = User::factory()->create();
    $tutor = Tutor::factory()->create(['user_id' => $user->id]);

    // Create initial address
    $address = Address::factory()->forTutor($tutor)->create([
        'postal_code' => '44100',
        'street' => 'Av. Vallarta',
        'external_number' => '1000',
        'neighborhood' => 'Americana',
        'type' => 'casa',
    ]);

    // Update address data
    $updateData = [
        'postal_code' => '44160',
        'street' => 'Av. Patria',
        'external_number' => '2500',
        'neighborhood' => 'Jardines del Bosque',
        'type' => 'departamento',
        'municipality' => 'Guadalajara',
        'state' => 'Jalisco',
    ];

    $response = $this->actingAs($user)->patch(route('addresses.update', $address->id), $updateData);

    $response->assertSessionHas('success');

    // Verify address was updated
    $address->refresh();
    expect($address->postal_code)->toBe('44160');
    expect($address->street)->toBe('Av. Patria');
    expect($address->external_number)->toBe('2500');
    expect($address->neighborhood)->toBe('Jardines del Bosque');
    expect($address->type->value)->toBe('departamento');
    expect($address->municipality)->toBe('Guadalajara');
    expect($address->state)->toBe('Jalisco');
});

test('address update fails with invalid external_number', function () {
    $user = User::factory()->create();
    $tutor = Tutor::factory()->create(['user_id' => $user->id]);

    $address = Address::factory()->forTutor($tutor)->create([
        'postal_code' => '44100',
        'street' => 'Av. Vallarta',
        'external_number' => '1000',
        'neighborhood' => 'Americana',
        'type' => 'casa',
    ]);

    // Try to update without external_number (required field)
    $updateData = [
        'postal_code' => '44160',
        'street' => 'Av. Patria',
        'external_number' => '', // Empty - should fail
        'neighborhood' => 'Jardines del Bosque',
        'type' => 'departamento',
    ];

    $response = $this->actingAs($user)->patch(route('addresses.update', $address->id), $updateData);

    $response->assertSessionHasErrors('external_number');

    // Verify address was NOT updated
    $address->refresh();
    expect($address->external_number)->toBe('1000');
    expect($address->street)->toBe('Av. Vallarta');
});

test('address update persists coordinates', function () {
    $user = User::factory()->create();
    $tutor = Tutor::factory()->create(['user_id' => $user->id]);

    $address = Address::factory()->forTutor($tutor)->create([
        'postal_code' => '44100',
        'street' => 'Av. Vallarta',
        'external_number' => '1000',
        'neighborhood' => 'Americana',
        'type' => 'casa',
        'latitude' => 20.676176,
        'longitude' => -103.347890,
    ]);

    // Update address with new coordinates
    $updateData = [
        'postal_code' => '44160',
        'street' => 'Av. Patria',
        'external_number' => '2500',
        'neighborhood' => 'Jardines del Bosque',
        'type' => 'departamento',
        'latitude' => 20.680000,
        'longitude' => -103.350000,
    ];

    $response = $this->actingAs($user)->patch(route('addresses.update', $address->id), $updateData);

    $response->assertSessionHas('success');

    // Verify coordinates were updated
    $address->refresh();
    expect($address->latitude)->toBe(20.680000);
    expect($address->longitude)->toBe(-103.350000);
});
