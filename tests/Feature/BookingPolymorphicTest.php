<?php

use App\Models\User;
use App\Models\Tutor;
use App\Models\Child;
use App\Models\Address;
use App\Models\Booking;
use App\Enums\Address\TypeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->tutor = Tutor::factory()->create(['user_id' => $this->user->id]);
    $this->actingAs($this->user);
});

test('can create booking with inline address (polymorphic)', function () {
    $child = Child::factory()->create(['tutor_id' => $this->tutor->id]);

    $response = $this->post(route('bookings.store'), [
        'booking' => [
            'tutor_id' => $this->tutor->id,
            'description' => 'Test booking with polymorphic address',
            'recurrent' => false,
            'children' => [(string) $child->id],
            'qualities' => ['Responsable', 'Paciente'],
            'degree' => 'Licenciatura',
            'courses' => ['Primeros Auxilios'],
        ],
        'appointments' => [
            [
                'start_date' => now()->addDay()->format('Y-m-d H:i:s'),
                'end_date' => now()->addDay()->addHours(4)->format('Y-m-d H:i:s'),
                'duration' => 4,
            ],
        ],
        'address' => [
            'postal_code' => '44100',
            'street' => 'Test Street 123',
            'neighborhood' => 'Test Neighborhood',
            'type' => 'Casa',
            'internal_number' => '5A',
        ],
    ]);

    $response->assertRedirect();
    
    $booking = Booking::latest()->first();
    expect($booking)->not->toBeNull()
        ->and($booking->description)->toBe('Test booking with polymorphic address')
        ->and($booking->qualities)->toBe(['Responsable', 'Paciente'])
        ->and($booking->degree)->toBe('Licenciatura')
        ->and($booking->courses)->toBe(['Primeros Auxilios']);

    // Check polymorphic address
    $address = $booking->address;
    expect($address)->not->toBeNull()
        ->and($address->addressable_type)->toBe('App\\Models\\Booking')
        ->and($address->addressable_id)->toBe($booking->id)
        ->and($address->postal_code)->toBe('44100')
        ->and($address->street)->toBe('Test Street 123');
});

test('can create booking with selected existing tutor address', function () {
    $child = Child::factory()->create(['tutor_id' => $this->tutor->id]);
    
    // Create an address for the tutor
    $address = Address::create([
        'postal_code' => '45050',
        'street' => 'Tutor Avenue 456',
        'neighborhood' => 'Tutor District',
        'type' => 'Departamento',
        'addressable_type' => 'App\\Models\\Tutor',
        'addressable_id' => $this->tutor->id,
    ]);

    $response = $this->post(route('bookings.store'), [
        'booking' => [
            'tutor_id' => $this->tutor->id,
            'address_id' => $address->id,
            'description' => 'Test booking with existing address',
            'recurrent' => false,
            'children' => [(string) $child->id],
        ],
        'appointments' => [
            [
                'start_date' => now()->addDay()->format('Y-m-d H:i:s'),
                'end_date' => now()->addDay()->addHours(3)->format('Y-m-d H:i:s'),
                'duration' => 3,
            ],
        ],
    ]);

    $response->assertRedirect();
    
    $booking = Booking::latest()->first();
    expect($booking)->not->toBeNull();
    
    // The address should now be reassociated to the booking
    $address->refresh();
    expect($address->addressable_type)->toBe('App\\Models\\Booking')
        ->and($address->addressable_id)->toBe($booking->id);
});

test('can update booking with new qualities and degree', function () {
    $child = Child::factory()->create(['tutor_id' => $this->tutor->id]);
    
    $booking = Booking::factory()->create([
        'tutor_id' => $this->tutor->id,
        'description' => 'Original description',
        'recurrent' => false,
    ]);
    
    $booking->children()->attach($child->id);
    
    Address::create([
        'postal_code' => '44100',
        'street' => 'Original Street',
        'neighborhood' => 'Original Hood',
        'type' => 'Casa',
        'addressable_type' => 'App\\Models\\Booking',
        'addressable_id' => $booking->id,
    ]);

    $response = $this->put(route('bookings.update', $booking), [
        'booking' => [
            'tutor_id' => $this->tutor->id,
            'description' => 'Updated description',
            'recurrent' => false,
            'child_ids' => [(string) $child->id],
            'qualities' => ['Creativa', 'Organizada', 'Comunicativa'],
            'degree' => 'Maestría',
            'courses' => ['Cuidado Infantil', 'Nutrición Infantil'],
        ],
        'appointments' => [
            [
                'start_date' => now()->addDay()->format('Y-m-d H:i:s'),
                'end_date' => now()->addDay()->addHours(5)->format('Y-m-d H:i:s'),
                'duration' => 5,
            ],
        ],
    ]);

    $response->assertRedirect();
    
    $booking->refresh();
    expect($booking->description)->toBe('Updated description')
        ->and($booking->qualities)->toBe(['Creativa', 'Organizada', 'Comunicativa'])
        ->and($booking->degree)->toBe('Maestría')
        ->and($booking->courses)->toBe(['Cuidado Infantil', 'Nutrición Infantil']);
});

test('tutor can have multiple addresses (polymorphic)', function () {
    $address1 = Address::create([
        'postal_code' => '44100',
        'street' => 'Street 1',
        'neighborhood' => 'Hood 1',
        'type' => 'Casa',
        'addressable_type' => 'App\\Models\\Tutor',
        'addressable_id' => $this->tutor->id,
    ]);

    $address2 = Address::create([
        'postal_code' => '44200',
        'street' => 'Street 2',
        'neighborhood' => 'Hood 2',
        'type' => 'Departamento',
        'addressable_type' => 'App\\Models\\Tutor',
        'addressable_id' => $this->tutor->id,
    ]);

    expect($this->tutor->addresses)->toHaveCount(2)
        ->and($this->tutor->addresses->pluck('id')->toArray())->toContain($address1->id, $address2->id);
});

test('booking has polymorphic address relation', function () {
    $booking = Booking::factory()->create([
        'tutor_id' => $this->tutor->id,
    ]);

    $address = Address::create([
        'postal_code' => '44300',
        'street' => 'Booking Street',
        'neighborhood' => 'Booking Hood',
        'type' => 'Casa',
        'addressable_type' => 'App\\Models\\Booking',
        'addressable_id' => $booking->id,
    ]);

    $booking->refresh();
    
    expect($booking->address)->not->toBeNull()
        ->and($booking->address->id)->toBe($address->id)
        ->and($booking->address->addressable_type)->toBe('App\\Models\\Booking')
        ->and($booking->address->addressable_id)->toBe($booking->id);
});
