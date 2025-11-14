<?php

namespace App\Policies;

use App\Enums\Permissions\BookingPermission;
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
        return $this->hasPermission($user, BookingPermission::ViewAny->value);
    }

    /**
     * Determine if the user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        // Check base permission
        if (! $this->hasPermission($user, BookingPermission::View->value)) {
            return false;
        }

        // Admin can view all
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Tutor can only view their own bookings
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $booking->loadMissing('tutor');

            return $booking->tutor?->user_id === $user->id;
        }

        // Nanny can view booking if assigned to at least one appointment
        if ($user->hasRole(RoleEnum::NANNY->value)) {
            return $booking->bookingAppointments()
                ->where('nanny_id', $user->nanny?->id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine if the user can create bookings.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(RoleEnum::TUTOR->value);
    }

    /**
     * Determine if the user can update the booking.
     */
    public function update(User $user, Booking $booking): bool
    {
        if (! $user->hasRole(RoleEnum::TUTOR->value)) {
            return false;
        }

        $booking->loadMissing('tutor');

        if ($booking->tutor_id !== $user->tutor?->id) {
            return false;
        }

        // Block edit if booking has nanny_id (old logic from before appointments)
        if ($booking->nanny_id !== null) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can delete the booking.
     */
    public function delete(User $user, Booking $booking): bool
    {
        if (! $user->hasRole(RoleEnum::TUTOR->value)) {
            return false;
        }

        $booking->loadMissing('tutor');

        if ($booking->tutor_id !== $user->tutor?->id) {
            return false;
        }

        // Block delete if any appointment has status != 'pending'
        $booking->loadMissing('bookingAppointments');
        $hasNonPendingAppointment = $booking->bookingAppointments->some(function ($appointment) {
            return $appointment->status->value !== 'pending';
        });

        if ($hasNonPendingAppointment) {
            return false;
        }

        return true;
    }

    private function hasPermission(User $user, string $permission): bool
    {
        $userRoles = $user->roles->pluck('name')->toArray();

        foreach ($userRoles as $roleName) {
            $roleEnum = $this->getRoleEnum($roleName);
            if ($roleEnum && BookingPermission::allows($permission, $roleEnum)) {
                return true;
            }
        }

        return false;
    }

    private function getRoleEnum(string $roleName): ?RoleEnum
    {
        return match (strtolower($roleName)) {
            'admin' => RoleEnum::ADMIN,
            'tutor' => RoleEnum::TUTOR,
            'nanny' => RoleEnum::NANNY,
            default => null,
        };
    }
}
