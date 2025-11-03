<?php

namespace App\Policies;

use App\Enums\User\RoleEnum;
use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine if the user can view any bookings.
     */
    public function viewAny(User $user): bool
    {
        // Admin can view all
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Tutor can view their own bookings
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        // Admin can view all
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Tutor can only view their own bookings
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $booking->loadMissing('tutor');

            return $booking->tutor?->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine if the user can create bookings.
     */
    public function create(User $user): bool
    {
        // Only tutors can create bookings
        return $user->hasRole(RoleEnum::TUTOR->value);
    }

    /**
     * Determine if the user can update the booking.
     */
    public function update(User $user, Booking $booking): bool
    {
        // Load appointments to check nanny assignments
        $booking->loadMissing('bookingAppointments');

        // Block edit if any appointment has a nanny assigned
        $hasNannyAssigned = $booking->bookingAppointments->some(function ($appointment) {
            return $appointment->nanny_id !== null;
        });

        if ($hasNannyAssigned) {
            return false;
        }

        // Block edit if any appointment is not in draft status
        $allDraft = $booking->bookingAppointments->every(function ($appointment) {
            return $appointment->status->value === 'draft';
        });

        if (! $allDraft) {
            return false;
        }

        // Admin can edit (if conditions above are met)
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Tutor can only edit their own bookings (if conditions above are met)
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $booking->loadMissing('tutor');

            return $booking->tutor?->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine if the user can delete the booking.
     */
    public function delete(User $user, Booking $booking): bool
    {
        // Load appointments to check status
        $booking->loadMissing('bookingAppointments');

        // Block delete if any appointment has a nanny assigned and is confirmed (not draft or pending)
        $hasConfirmedNanny = $booking->bookingAppointments->some(function ($appointment) {
            return $appointment->nanny_id !== null
                && ! in_array($appointment->status->value, ['draft', 'pending']);
        });

        if ($hasConfirmedNanny) {
            return false;
        }

        // Admin can delete (if conditions above are met)
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Tutor can only delete their own bookings (if conditions above are met)
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $booking->loadMissing('tutor');

            return $booking->tutor?->user_id === $user->id;
        }

        return false;
    }
}
