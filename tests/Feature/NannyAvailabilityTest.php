<?php

use App\Models\Booking;
use App\Models\BookingAppointment;
use App\Models\Nanny;
use App\Models\Tutor;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles if they don't exist
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'nanny']);
    Role::firstOrCreate(['name' => 'tutor']);
});

test('nanny is available when no appointments overlap', function () {
    $nanny = Nanny::factory()->create();

    // Create an existing appointment
    $tutor = Tutor::factory()->create();
    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => $nanny->id,
        'start_date' => '2025-01-01 08:00:00',
        'end_date' => '2025-01-01 12:00:00',
    ]);

    // Check availability for a non-overlapping time slot
    $available = Nanny::availableBetween('2025-01-01 13:00:00', '2025-01-01 17:00:00')
        ->where('id', $nanny->id)
        ->exists();

    expect($available)->toBeTrue();
});

test('nanny is not available when appointments overlap at start', function () {
    $nanny = Nanny::factory()->create();

    // Create an existing appointment
    $tutor = Tutor::factory()->create();
    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => $nanny->id,
        'start_date' => '2025-01-01 08:00:00',
        'end_date' => '2025-01-01 12:00:00',
    ]);

    // Check availability for an overlapping time slot (starts during existing appointment)
    $available = Nanny::availableBetween('2025-01-01 10:00:00', '2025-01-01 14:00:00')
        ->where('id', $nanny->id)
        ->exists();

    expect($available)->toBeFalse();
});

test('nanny is not available when appointments overlap at end', function () {
    $nanny = Nanny::factory()->create();

    // Create an existing appointment
    $tutor = Tutor::factory()->create();
    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => $nanny->id,
        'start_date' => '2025-01-01 08:00:00',
        'end_date' => '2025-01-01 12:00:00',
    ]);

    // Check availability for an overlapping time slot (ends during existing appointment)
    $available = Nanny::availableBetween('2025-01-01 06:00:00', '2025-01-01 10:00:00')
        ->where('id', $nanny->id)
        ->exists();

    expect($available)->toBeFalse();
});

test('nanny is not available when new appointment is within existing appointment', function () {
    $nanny = Nanny::factory()->create();

    // Create an existing appointment
    $tutor = Tutor::factory()->create();
    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => $nanny->id,
        'start_date' => '2025-01-01 08:00:00',
        'end_date' => '2025-01-01 12:00:00',
    ]);

    // Check availability for a time slot completely within existing appointment
    $available = Nanny::availableBetween('2025-01-01 09:00:00', '2025-01-01 11:00:00')
        ->where('id', $nanny->id)
        ->exists();

    expect($available)->toBeFalse();
});

test('nanny is not available when new appointment encompasses existing appointment', function () {
    $nanny = Nanny::factory()->create();

    // Create an existing appointment
    $tutor = Tutor::factory()->create();
    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => $nanny->id,
        'start_date' => '2025-01-01 10:00:00',
        'end_date' => '2025-01-01 12:00:00',
    ]);

    // Check availability for a time slot that encompasses existing appointment
    $available = Nanny::availableBetween('2025-01-01 08:00:00', '2025-01-01 14:00:00')
        ->where('id', $nanny->id)
        ->exists();

    expect($available)->toBeFalse();
});

test('nanny is available when they have no appointments', function () {
    $nanny = Nanny::factory()->create();

    // Check availability with no existing appointments
    $available = Nanny::availableBetween('2025-01-01 08:00:00', '2025-01-01 12:00:00')
        ->where('id', $nanny->id)
        ->exists();

    expect($available)->toBeTrue();
});
