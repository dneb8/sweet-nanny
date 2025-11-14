<?php

use App\Models\Booking;
use App\Models\BookingAppointment;
use App\Models\Nanny;
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

test('admin can view any bookings', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect(Gate::forUser($admin)->allows('viewAny', Booking::class))->toBeTrue();
});

test('tutor can view any bookings', function () {
    $user = User::factory()->create();
    $user->assignRole('tutor');

    expect(Gate::forUser($user)->allows('viewAny', Booking::class))->toBeTrue();
});

test('nanny cannot view any bookings', function () {
    $nanny = User::factory()->create();
    $nanny->assignRole('nanny');

    expect(Gate::forUser($nanny)->allows('viewAny', Booking::class))->toBeFalse();
});

test('admin can view any booking', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $tutor = Tutor::factory()->create();
    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);

    expect(Gate::forUser($admin)->allows('view', $booking))->toBeTrue();
});

test('tutor can view their own booking', function () {
    $user = User::factory()->create();
    $user->assignRole('tutor');
    $tutor = Tutor::factory()->create(['user_id' => $user->id]);

    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);

    expect(Gate::forUser($user)->allows('view', $booking))->toBeTrue();
});

test('tutor cannot view other tutors booking', function () {
    $user = User::factory()->create();
    $user->assignRole('tutor');
    $tutor = Tutor::factory()->create(['user_id' => $user->id]);

    $otherTutor = Tutor::factory()->create();
    $booking = Booking::factory()->create(['tutor_id' => $otherTutor->id]);

    expect(Gate::forUser($user)->allows('view', $booking))->toBeFalse();
});

test('only tutor can create bookings', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $tutor = User::factory()->create();
    $tutor->assignRole('tutor');

    $nanny = User::factory()->create();
    $nanny->assignRole('nanny');

    expect(Gate::forUser($admin)->allows('create', Booking::class))->toBeFalse();
    expect(Gate::forUser($tutor)->allows('create', Booking::class))->toBeTrue();
    expect(Gate::forUser($nanny)->allows('create', Booking::class))->toBeFalse();
});

test('tutor can update their booking when no nanny assigned and all draft', function () {
    $user = User::factory()->create();
    $user->assignRole('tutor');
    $tutor = Tutor::factory()->create(['user_id' => $user->id]);

    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => null,
        'status' => 'draft',
    ]);

    expect(Gate::forUser($user)->allows('update', $booking))->toBeTrue();
});

test('tutor cannot update booking when nanny is assigned', function () {
    $user = User::factory()->create();
    $user->assignRole('tutor');
    $tutor = Tutor::factory()->create(['user_id' => $user->id]);
    $nanny = Nanny::factory()->create();

    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => $nanny->id,
        'status' => 'pending',
    ]);

    expect(Gate::forUser($user)->allows('update', $booking))->toBeFalse();
});

test('tutor cannot update booking when appointment is not draft', function () {
    $user = User::factory()->create();
    $user->assignRole('tutor');
    $tutor = Tutor::factory()->create(['user_id' => $user->id]);

    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => null,
        'status' => 'pending',
    ]);

    expect(Gate::forUser($user)->allows('update', $booking))->toBeFalse();
});

test('admin cannot update booking when nanny is assigned', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $tutor = Tutor::factory()->create();
    $nanny = Nanny::factory()->create();

    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => $nanny->id,
        'status' => 'pending',
    ]);

    expect(Gate::forUser($admin)->allows('update', $booking))->toBeFalse();
});

test('tutor can delete booking with draft appointments', function () {
    $user = User::factory()->create();
    $user->assignRole('tutor');
    $tutor = Tutor::factory()->create(['user_id' => $user->id]);

    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => null,
        'status' => 'draft',
    ]);

    expect(Gate::forUser($user)->allows('delete', $booking))->toBeTrue();
});

test('tutor can delete booking with pending appointments', function () {
    $user = User::factory()->create();
    $user->assignRole('tutor');
    $tutor = Tutor::factory()->create(['user_id' => $user->id]);
    $nanny = Nanny::factory()->create();

    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => $nanny->id,
        'status' => 'pending',
    ]);

    expect(Gate::forUser($user)->allows('delete', $booking))->toBeTrue();
});

test('tutor cannot delete booking with confirmed appointments', function () {
    $user = User::factory()->create();
    $user->assignRole('tutor');
    $tutor = Tutor::factory()->create(['user_id' => $user->id]);
    $nanny = Nanny::factory()->create();

    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => $nanny->id,
        'status' => 'confirmed',
    ]);

    expect(Gate::forUser($user)->allows('delete', $booking))->toBeFalse();
});

test('admin cannot delete booking with confirmed appointments', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $tutor = Tutor::factory()->create();
    $nanny = Nanny::factory()->create();

    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => $nanny->id,
        'status' => 'confirmed',
    ]);

    expect(Gate::forUser($admin)->allows('delete', $booking))->toBeFalse();
});

test('tutor cannot delete other tutors booking', function () {
    $user = User::factory()->create();
    $user->assignRole('tutor');
    $tutor = Tutor::factory()->create(['user_id' => $user->id]);

    $otherTutor = Tutor::factory()->create();
    $booking = Booking::factory()->create(['tutor_id' => $otherTutor->id]);
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => null,
        'status' => 'draft',
    ]);

    expect(Gate::forUser($user)->allows('delete', $booking))->toBeFalse();
});

test('nanny can view booking with their appointment', function () {
    $nannyUser = User::factory()->create();
    $nannyUser->assignRole('nanny');
    $nanny = Nanny::factory()->create(['user_id' => $nannyUser->id]);

    $tutor = Tutor::factory()->create();
    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => $nanny->id,
        'status' => 'pending',
    ]);

    expect(Gate::forUser($nannyUser)->allows('view', $booking))->toBeTrue();
});

test('nanny cannot view booking without their appointment', function () {
    $nannyUser = User::factory()->create();
    $nannyUser->assignRole('nanny');
    $nanny = Nanny::factory()->create(['user_id' => $nannyUser->id]);

    $otherNanny = Nanny::factory()->create();

    $tutor = Tutor::factory()->create();
    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => $otherNanny->id,
        'status' => 'pending',
    ]);

    expect(Gate::forUser($nannyUser)->allows('view', $booking))->toBeFalse();
});

test('nanny can view booking with multiple appointments if at least one is theirs', function () {
    $nannyUser = User::factory()->create();
    $nannyUser->assignRole('nanny');
    $nanny = Nanny::factory()->create(['user_id' => $nannyUser->id]);

    $otherNanny = Nanny::factory()->create();

    $tutor = Tutor::factory()->create();
    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);

    // Create appointment for other nanny
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => $otherNanny->id,
        'status' => 'pending',
    ]);

    // Create appointment for current nanny
    BookingAppointment::factory()->create([
        'booking_id' => $booking->id,
        'nanny_id' => $nanny->id,
        'status' => 'confirmed',
    ]);

    expect(Gate::forUser($nannyUser)->allows('view', $booking))->toBeTrue();
});

test('nanny cannot view booking with no appointments', function () {
    $nannyUser = User::factory()->create();
    $nannyUser->assignRole('nanny');
    Nanny::factory()->create(['user_id' => $nannyUser->id]);

    $tutor = Tutor::factory()->create();
    $booking = Booking::factory()->create(['tutor_id' => $tutor->id]);

    expect(Gate::forUser($nannyUser)->allows('view', $booking))->toBeFalse();
});
