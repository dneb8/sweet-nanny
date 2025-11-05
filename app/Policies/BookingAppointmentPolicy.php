<?php

namespace App\Policies;

use App\Enums\Booking\StatusEnum;
use App\Enums\Permissions\BookingAppointmentPermission as Perm;
use App\Enums\User\RoleEnum;
use App\Models\BookingAppointment;
use App\Models\User;

class BookingAppointmentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        // Admin pasa todo
        if ($this->hasRole($user, RoleEnum::ADMIN)) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, Perm::ViewAny->value);
    }

    public function view(User $user, BookingAppointment $appointment): bool
    {
        if ($this->hasPermission($user, Perm::View->value)) {
            return true;
        }

        $appointment->loadMissing('booking.tutor', 'nanny');
        return
            (optional($appointment->booking?->tutor)->user_id === $user->id) ||
            (optional($appointment->nanny)->user_id === $user->id);
    }

    public function chooseNanny(User $user, BookingAppointment $appointment): bool
    {
        if (! $this->hasPermission($user, Perm::ChooseNanny->value)) return false;

        $appointment->loadMissing('booking.tutor');
        $isOwner = (optional($appointment->booking?->tutor)->user_id === $user->id);

        return $isOwner && is_null($appointment->nanny_id);
    }

    public function assignNanny(User $user, BookingAppointment $appointment): bool
    {
        if (! $this->hasPermission($user, Perm::AssignNanny->value)) return false;

        $appointment->loadMissing('booking.tutor');
        return (optional($appointment->booking?->tutor)->user_id === $user->id);
    }

    public function cancel(User $user, BookingAppointment $appointment): bool
    {
        if (! $this->hasPermission($user, Perm::Cancel->value)) return false;

        $appointment->loadMissing('booking.tutor');
        $isOwner = (optional($appointment->booking?->tutor)->user_id === $user->id);

        return $isOwner && ($appointment->status->value !== StatusEnum::COMPLETED->value);
    }

    public function unassignNanny(User $user, BookingAppointment $appointment): bool
    {
        if (! $this->hasPermission($user, Perm::UnassignNanny->value)) return false;

        $appointment->loadMissing('booking.tutor', 'nanny');
        $isOwnerTutor    = (optional($appointment->booking?->tutor)->user_id === $user->id);
        $isAssignedNanny = (optional($appointment->nanny)->user_id === $user->id);

        return ($isOwnerTutor || $isAssignedNanny)
            && ($appointment->status->value === StatusEnum::CONFIRMED->value)
            && ! is_null($appointment->nanny_id);
    }

    public function accept(User $user, BookingAppointment $appointment): bool
    {
        if (! $this->hasPermission($user, Perm::Accept->value)) return false;

        $appointment->loadMissing('nanny');
        return (optional($appointment->nanny)->user_id === $user->id)
            && ($appointment->status->value === StatusEnum::PENDING->value);
    }

    public function reject(User $user, BookingAppointment $appointment): bool
    {
        if (! $this->hasPermission($user, Perm::Reject->value)) return false;

        $appointment->loadMissing('nanny');
        return (optional($appointment->nanny)->user_id === $user->id)
            && ($appointment->status->value === StatusEnum::PENDING->value);
    }

    public function update(User $user, BookingAppointment $appointment): bool
    {
        if (! $this->hasPermission($user, Perm::UpdateDates->value)) return false; // gating mínimo

        if (! in_array($appointment->status->value, [StatusEnum::DRAFT->value, StatusEnum::PENDING->value], true)) {
            return false;
        }

        $appointment->loadMissing('booking.tutor');
        return (optional($appointment->booking?->tutor)->user_id === $user->id);
    }

    public function updateDates(User $user, BookingAppointment $appointment): bool
    {
        return $this->hasPermission($user, Perm::UpdateDates->value) && $this->update($user, $appointment);
    }

    public function updateAddress(User $user, BookingAppointment $appointment): bool
    {
        return $this->hasPermission($user, Perm::UpdateAddress->value) && $this->update($user, $appointment);
    }

    public function updateChildren(User $user, BookingAppointment $appointment): bool
    {
        return $this->hasPermission($user, Perm::UpdateChildren->value) && $this->update($user, $appointment);
    }

    public function delete(User $user, BookingAppointment $appointment): bool
    {
        return $this->hasPermission($user, Perm::Delete->value);
    }

    private function hasPermission(User $user, string $permission): bool
    {
        // obtiene roles del usuario y consulta el enum map
        $userRoles = $user->roles->pluck('name')->toArray();

        foreach ($userRoles as $roleName) {
            if ($roleEnum = $this->getRoleEnum($roleName)) {
                if (Perm::allows($permission, $roleEnum)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function hasRole(User $user, RoleEnum $role): bool
    {
        return $user->hasRole($role->value);
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
