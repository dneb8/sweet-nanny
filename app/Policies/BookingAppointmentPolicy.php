<?php

namespace App\Policies;

use App\Enums\User\RoleEnum;
use App\Models\BookingAppointment;
use App\Models\User;

class BookingAppointmentPolicy
{
    public function chooseNanny(User $user, BookingAppointment $appointment): bool
    {
        // 1) ADMIN: always can choose nanny
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // 2) TUTOR: can choose nanny only if:
        //    - They are the owner of the booking
        //    - The appointment doesn't have a nanny assigned yet
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            // Check if nanny is already assigned
            if ($appointment->nanny_id !== null) {
                return false;
            }

            // Load the booking and tutor relationship
            $appointment->loadMissing('booking.tutor');
            $bookingTutorUserId = $appointment->booking?->tutor?->user_id;

            // Verify the user owns the booking
            return $bookingTutorUserId !== null && (int)$bookingTutorUserId === (int)$user->id;
        }

        // 3) NANNY or other roles: cannot choose nanny
        return false;
    }

    /**
     * Determine if the user can cancel an appointment
     */
    public function cancel(User $user, BookingAppointment $appointment): bool
    {
        // Admin can cancel any appointment
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Tutor can cancel their own appointments
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $bookingTutorUserId = $appointment->booking?->tutor?->user_id;
            return $bookingTutorUserId !== null && (int)$bookingTutorUserId === (int)$user->id;
        }

        return false;
    }

    /**
     * Determine if the user can update an appointment
     */
    public function update(User $user, BookingAppointment $appointment): bool
    {
        // Check status - only draft and pending can be edited
        if (!in_array($appointment->status, ['draft', 'pending'])) {
            return false;
        }

        // Admin can edit any
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Tutor can edit their own
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $bookingTutorUserId = $appointment->booking?->tutor?->user_id;
            return $bookingTutorUserId !== null && (int)$bookingTutorUserId === (int)$user->id;
        }

        return false;
    }
}
