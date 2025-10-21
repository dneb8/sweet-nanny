<?php

use App\Models\User;
use App\Models\Tutor;
use App\Models\Child;
use App\Models\Address;
use App\Models\Booking;
use App\Enums\Address\TypeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles first
    Role::create(['name' => 'tutor', 'guard_name' => 'web']);
    Role::create(['name' => 'nanny', 'guard_name' => 'web']);
    
    $this->user = User::factory()->create();
    $this->tutor = Tutor::factory()->create(['user_id' => $this->user->id]);
    $this->actingAs($this->user);
});

test('can create booking with new fields', function () {
    $child = Child::factory()->create(['tutor_id' => $this->tutor->id]);

    $booking = Booking::create([
        'tutor_id' => $this->tutor->id,
        'description' => 'Test booking with new fields',
        'recurrent' => false,
        'qualities' => ['Responsable', 'Paciente'],
        'degree' => 'Licenciatura',
        'courses' => ['Primeros Auxilios'],
    ]);
    
    $booking->children()->attach($child->id);

    expect($booking)->not->toBeNull()
        ->and($booking->description)->toBe('Test booking with new fields')
        ->and($booking->qualities)->toBe(['Responsable', 'Paciente'])
        ->and($booking->degree)->toBe('Licenciatura')
        ->and($booking->courses)->toBe(['Primeros Auxilios']);
});

test('booking can have polymorphic address', function () {
    $booking = Booking::create([
        'tutor_id' => $this->tutor->id,
        'description' => 'Test booking',
        'recurrent' => false,
    ]);
    
    // Create an address for the booking
    $address = Address::create([
        'postal_code' => '45050',
        'street' => 'Booking Avenue 456',
        'neighborhood' => 'Booking District',
        'type' => 'departamento',
        'addressable_type' => 'App\\Models\\Booking',
        'addressable_id' => $booking->id,
    ]);

    $booking->refresh();
    
    // Check polymorphic relationship
    expect($booking->address)->not->toBeNull()
        ->and($booking->address->id)->toBe($address->id)
        ->and($booking->address->addressable_type)->toBe('App\\Models\\Booking')
        ->and($booking->address->addressable_id)->toBe($booking->id);
});

test('can update booking with new qualities and degree', function () {
    $booking = Booking::create([
        'tutor_id' => $this->tutor->id,
        'description' => 'Original description',
        'recurrent' => false,
        'qualities' => ['Responsable'],
        'degree' => 'Licenciatura',
        'courses' => ['Primeros Auxilios'],
    ]);
    
    $booking->update([
        'qualities' => ['Creativa', 'Organizada', 'Comunicativa'],
        'degree' => 'Maestría',
        'courses' => ['Cuidado Infantil', 'Nutrición Infantil'],
    ]);

    $booking->refresh();
    expect($booking->qualities)->toBe(['Creativa', 'Organizada', 'Comunicativa'])
        ->and($booking->degree)->toBe('Maestría')
        ->and($booking->courses)->toBe(['Cuidado Infantil', 'Nutrición Infantil']);
});

test('tutor can have multiple addresses (polymorphic)', function () {
    $address1 = Address::create([
        'postal_code' => '44100',
        'street' => 'Street 1',
        'neighborhood' => 'Hood 1',
        'type' => 'casa',
        'addressable_type' => 'App\\Models\\Tutor',
        'addressable_id' => $this->tutor->id,
    ]);

    $address2 = Address::create([
        'postal_code' => '44200',
        'street' => 'Street 2',
        'neighborhood' => 'Hood 2',
        'type' => 'departamento',
        'addressable_type' => 'App\\Models\\Tutor',
        'addressable_id' => $this->tutor->id,
    ]);

    expect($this->tutor->addresses)->toHaveCount(2)
        ->and($this->tutor->addresses->pluck('id')->toArray())->toContain($address1->id, $address2->id);
});

test('address polymorphic relation works correctly', function () {
    $booking = Booking::create([
        'tutor_id' => $this->tutor->id,
        'description' => 'Test',
        'recurrent' => false,
    ]);

    $address = Address::create([
        'postal_code' => '44300',
        'street' => 'Booking Street',
        'neighborhood' => 'Booking Hood',
        'type' => 'casa',
        'addressable_type' => 'App\\Models\\Booking',
        'addressable_id' => $booking->id,
    ]);

    $address->refresh();
    
    // Check addressable relation
    expect($address->addressable)->not->toBeNull()
        ->and($address->addressable)->toBeInstanceOf(Booking::class)
        ->and($address->addressable->id)->toBe($booking->id);
});
