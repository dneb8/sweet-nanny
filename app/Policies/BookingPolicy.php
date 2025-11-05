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

        return false;
    }

    /**
     * Determine if the user can create bookings.
     */
    public function create(User $user): bool
    {
        return $this->hasPermission($user, BookingPermission::Create->value);
    }

    /**
     * Determine if the user can update the booking.
     */
    public function update(User $user, Booking $booking): bool
    {
        // Check base permission
        if (! $this->hasPermission($user, BookingPermission::Update->value)) {
            return false;
        }

        // Load appointments to check nanny assignments
        $booking->loadMissing('bookingAppointments');

        // Block edit if any appointment has a nanny assigned
        $hasNannyAssigned = $booking->bookingAppointments->some(function ($appointment) {
            return $appointment->nanny_id !== null;
        });

        if ($hasNannyAssigned) {
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
        // Check base permission
        if (! $this->hasPermission($user, BookingPermission::Delete->value)) {
            return false;
        }

        // Load appointments to check status
        $booking->loadMissing('bookingAppointments');

        // Block delete if any appointment has a nanny assigned
        $hasNannyAssigned = $booking->bookingAppointments->some(function ($appointment) {
            return $appointment->nanny_id !== null;
        });

        if ($hasNannyAssigned) {
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
