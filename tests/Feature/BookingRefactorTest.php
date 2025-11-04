<?php

use App\Models\Address;
use App\Models\Booking;
use App\Models\BookingAppointment;
use App\Models\Child;
use App\Models\Tutor;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    Role::create(['name' => 'tutor']);
    Role::create(['name' => 'nanny']);

    // Create a user and tutor
    $this->user = User::factory()->create();
    $this->tutor = Tutor::factory()->create([
        'user_id' => $this->user->id,
    ]);

    // Create an address for the tutor
    $this->address = Address::factory()->create([
        'addressable_type' => 'App\Models\Tutor',
        'addressable_id' => $this->tutor->id,
        'street' => 'Test Street',
        'neighborhood' => 'Test Neighborhood',
        'postal_code' => '12345',
    ]);

    // Create children for the tutor
    $this->child1 = Child::factory()->create([
        'tutor_id' => $this->tutor->id,
        'name' => 'Child One',
    ]);
    $this->child2 = Child::factory()->create([
        'tutor_id' => $this->tutor->id,
        'name' => 'Child Two',
    ]);
});

test('booking does not have address relationship', function () {
    $booking = Booking::factory()->create([
        'tutor_id' => $this->tutor->id,
    ]);

    expect(method_exists($booking, 'address'))->toBeFalse();
    expect(method_exists($booking, 'children'))->toBeFalse();
});

test('booking appointment has address and children relationships', function () {
    $booking = Booking::factory()->create([
        'tutor_id' => $this->tutor->id,
    ]);

    $appointment = BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
    ]);

    expect(method_exists($appointment, 'addresses'))->toBeTrue();
    expect(method_exists($appointment, 'children'))->toBeTrue();
});

test('booking service creates appointments with address and children', function () {
    $service = new BookingService();

    $payload = [
        'booking' => [
            'tutor_id' => $this->tutor->id,
            'address_id' => $this->address->id,
            'description' => 'Test booking description',
            'recurrent' => false,
            'child_ids' => [$this->child1->id, $this->child2->id],
            'qualities' => [],
            'careers' => [],
            'courses' => [],
        ],
        'appointments' => [
            [
                'start_date' => now()->addDays(1),
                'end_date' => now()->addDays(1)->addHours(2),
                'duration' => 2,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'extra_hours' => 0,
                'total_cost' => 100.00,
            ],
        ],
    ];

    $booking = $service->create($payload);

    // Verify booking was created without address/children
    expect($booking)->toBeInstanceOf(Booking::class);
    expect($booking->description)->toBe('Test booking description');

    // Verify appointment was created
    expect($booking->bookingAppointments)->toHaveCount(1);

    // Verify address was applied to appointment
    $appointment = $booking->bookingAppointments->first();
    expect($appointment->addresses)->toHaveCount(1);
    expect($appointment->addresses->first()->id)->toBe($this->address->id);

    // Verify children were applied to appointment
    expect($appointment->children)->toHaveCount(2);
    expect($appointment->children->pluck('id')->sort()->values()->toArray())
        ->toBe([$this->child1->id, $this->child2->id]);
});

test('booking service creates multiple appointments with same address and children', function () {
    $service = new BookingService();

    $payload = [
        'booking' => [
            'tutor_id' => $this->tutor->id,
            'address_id' => $this->address->id,
            'description' => 'Test recurrent booking',
            'recurrent' => true,
            'child_ids' => [$this->child1->id],
            'qualities' => [],
            'careers' => [],
            'courses' => [],
        ],
        'appointments' => [
            [
                'start_date' => now()->addDays(1),
                'end_date' => now()->addDays(1)->addHours(2),
                'duration' => 2,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'extra_hours' => 0,
                'total_cost' => 100.00,
            ],
            [
                'start_date' => now()->addDays(8),
                'end_date' => now()->addDays(8)->addHours(2),
                'duration' => 2,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'extra_hours' => 0,
                'total_cost' => 100.00,
            ],
        ],
    ];

    $booking = $service->create($payload);

    // Verify 2 appointments were created
    expect($booking->bookingAppointments)->toHaveCount(2);

    // Verify each appointment has the same address
    foreach ($booking->bookingAppointments as $appointment) {
        expect($appointment->addresses)->toHaveCount(1);
        expect($appointment->addresses->first()->id)->toBe($this->address->id);

        expect($appointment->children)->toHaveCount(1);
        expect($appointment->children->first()->id)->toBe($this->child1->id);
    }
});
