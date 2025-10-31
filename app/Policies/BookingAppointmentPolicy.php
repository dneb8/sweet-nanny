<?php

namespace App\Policies;

use App\Enums\Permissions\BookingAppointmentPermission;
use App\Enums\User\RoleEnum;
use App\Models\BookingAppointment;
use App\Models\User;

class BookingAppointmentPolicy
{
    /**
     * Determine if the user can choose a nanny for the appointment.
     */
    public function chooseNanny(User $user, BookingAppointment $appointment): bool
    {
        // Check if user has the permission based on role
        if (! $this->hasPermission($user, BookingAppointmentPermission::ChooseNanny->value)) {
            return false;
        }

        // If user is Admin, allow choosing nanny for any appointment
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // If user is Tutor, only allow for their own bookings
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->load('booking.tutor');

            return $appointment->booking?->tutor?->user_id === $user->id;
        }

        return false;
    }

    /**
     * Check if the user has the required permission based on their roles.
     */
    private function hasPermission(User $user, string $permission): bool
    {
        $userRoles = $user->roles->pluck('name')->toArray();

        foreach ($userRoles as $roleName) {
            $roleEnum = $this->getRoleEnum($roleName);
            // Check permission using Laravel's built-in can() method
            if ($roleEnum && $user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert role name string to RoleEnum
     */
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
