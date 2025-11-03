<?php

use App\Models\Booking;
use App\Models\BookingAppointment;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles if they don't exist
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'nanny']);
    Role::firstOrCreate(['name' => 'tutor']);
});

test('admin can choose nanny for any appointment', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $tutor = Tutor::factory()->create();
    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    $appointment = BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => null,
    ]);

    expect(Gate::forUser($admin)->allows('chooseNanny', $appointment))->toBeTrue();
});

test('tutor can choose nanny for their own appointments', function () {
    $user = User::factory()->create();
    $user->assignRole('tutor');
    $tutor = Tutor::factory()->create(['user_id' => $user->id]);

    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    $appointment = BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => null,
    ]);

    expect(Gate::forUser($user)->allows('chooseNanny', $appointment))->toBeTrue();
});

test('tutor cannot choose nanny for other tutors appointments', function () {
    $user = User::factory()->create();
    $user->assignRole('tutor');
    $tutor = Tutor::factory()->create(['user_id' => $user->id]);

    $otherTutor = Tutor::factory()->create();
    $booking = Booking::factory()->create(['tutor_id' => $otherTutor->id]);
    $appointment = BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => null,
    ]);

    expect(Gate::forUser($user)->allows('chooseNanny', $appointment))->toBeFalse();
});

test('nanny cannot choose nanny for appointments', function () {
    $nanny = User::factory()->create();
    $nanny->assignRole('nanny');

    $tutor = Tutor::factory()->create();
    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    $appointment = BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => null,
    ]);

    expect(Gate::forUser($nanny)->allows('chooseNanny', $appointment))->toBeFalse();
});
