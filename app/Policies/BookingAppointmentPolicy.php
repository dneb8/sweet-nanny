<?php

namespace App\Policies;

use App\Enums\Booking\StatusEnum;
use App\Enums\Permissions\BookingAppointmentPermission;
use App\Enums\User\RoleEnum;
use App\Models\BookingAppointment;
use App\Models\User;

class BookingAppointmentPolicy
{
    /**
     * Determine if the user can view any booking appointments
     */
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, BookingAppointmentPermission::ViewAny->value);
    }

    /**
     * Determine if the user can view a specific booking appointment
     */
    public function view(User $user, BookingAppointment $appointment): bool
    {
        // Check base permission
        if (! $this->hasPermission($user, BookingAppointmentPermission::View->value)) {
            return false;
        }

        // Admin can view any appointment
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Nanny can only view appointments assigned to them
        if ($user->hasRole(RoleEnum::NANNY->value)) {
            if ($appointment->nanny_id === null) {
                return false;
            }
            $appointment->loadMissing('nanny');

            return (int) $appointment->nanny->user_id === (int) $user->id;
        }

        // Tutor can view their own appointments
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $bookingTutorUserId = $appointment->booking?->tutor?->user_id;

            return $bookingTutorUserId !== null && (int) $bookingTutorUserId === (int) $user->id;
        }

        return false;
    }

    /**
     * Determine if the user can choose/browse nannies for an appointment
     * Only when appointment is in draft status and has no nanny assigned
     */
    public function chooseNanny(User $user, BookingAppointment $appointment): bool
    {
        // Check base permission
        if (! $this->hasPermission($user, BookingAppointmentPermission::ChooseNanny->value)) {
            return false;
        }

        // Must be in draft status
        if ($appointment->status->value !== StatusEnum::DRAFT->value) {
            return false;
        }

        // Must not have a nanny assigned
        if ($appointment->nanny_id !== null) {
            return false;
        }

        // Admin can always choose
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Tutor can choose for their own bookings
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $bookingTutorUserId = $appointment->booking?->tutor?->user_id;

            return $bookingTutorUserId !== null && (int) $bookingTutorUserId === (int) $user->id;
        }

        return false;
    }

    /**
     * Determine if the user can assign a nanny to an appointment
     * Only when appointment is in draft status
     */
    public function assignNanny(User $user, BookingAppointment $appointment): bool
    {
        // Check base permission
        if (! $this->hasPermission($user, BookingAppointmentPermission::AssignNanny->value)) {
            return false;
        }

        // Must be in draft status
        if ($appointment->status->value !== StatusEnum::DRAFT->value) {
            return false;
        }

        // Admin can always assign
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Tutor can assign for their own bookings
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $bookingTutorUserId = $appointment->booking?->tutor?->user_id;

            return $bookingTutorUserId !== null && (int) $bookingTutorUserId === (int) $user->id;
        }

        return false;
    }

    /**
     * Determine if the user can update dates on an appointment
     * Only in draft or pending status; will unassign nanny if in pending
     */
    public function updateDates(User $user, BookingAppointment $appointment): bool
    {
        // Check base permission
        if (! $this->hasPermission($user, BookingAppointmentPermission::UpdateDates->value)) {
            return false;
        }

        // Only draft or pending can update dates
        if (! in_array($appointment->status->value, [StatusEnum::DRAFT->value, StatusEnum::PENDING->value])) {
            return false;
        }

        // Admin can always update
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Tutor can update for their own bookings
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $bookingTutorUserId = $appointment->booking?->tutor?->user_id;

            return $bookingTutorUserId !== null && (int) $bookingTutorUserId === (int) $user->id;
        }

        return false;
    }

    /**
     * Determine if the user can update address on an appointment
     * Only in draft or pending status; will unassign nanny if in pending
     */
    public function updateAddress(User $user, BookingAppointment $appointment): bool
    {
        // Check base permission
        if (! $this->hasPermission($user, BookingAppointmentPermission::UpdateAddress->value)) {
            return false;
        }

        // Only draft or pending can update address
        if (! in_array($appointment->status->value, [StatusEnum::DRAFT->value, StatusEnum::PENDING->value])) {
            return false;
        }

        // Admin can always update
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Tutor can update for their own bookings
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $bookingTutorUserId = $appointment->booking?->tutor?->user_id;

            return $bookingTutorUserId !== null && (int) $bookingTutorUserId === (int) $user->id;
        }

        return false;
    }

    /**
     * Determine if the user can update children on an appointment
     * Only in draft or pending status; will unassign nanny if in pending
     */
    public function updateChildren(User $user, BookingAppointment $appointment): bool
    {
        // Check base permission
        if (! $this->hasPermission($user, BookingAppointmentPermission::UpdateChildren->value)) {
            return false;
        }

        // Only draft or pending can update children
        if (! in_array($appointment->status->value, [StatusEnum::DRAFT->value, StatusEnum::PENDING->value])) {
            return false;
        }

        // Admin can always update
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Tutor can update for their own bookings
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $bookingTutorUserId = $appointment->booking?->tutor?->user_id;

            return $bookingTutorUserId !== null && (int) $bookingTutorUserId === (int) $user->id;
        }

        return false;
    }

    /**
     * Determine if the nanny can accept a pending appointment
     * Only when status is pending and nanny is assigned
     */
    public function accept(User $user, BookingAppointment $appointment): bool
    {
        // Check base permission
        if (! $this->hasPermission($user, BookingAppointmentPermission::Accept->value)) {
            return false;
        }

        // Must be in pending status
        if ($appointment->status->value !== StatusEnum::PENDING->value) {
            return false;
        }

        // Must have a nanny assigned
        if ($appointment->nanny_id === null) {
            return false;
        }

        // Admin can always accept
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Nanny can only accept their own assignments
        if ($user->hasRole(RoleEnum::NANNY->value)) {
            $appointment->loadMissing('nanny');

            return (int) $appointment->nanny->user_id === (int) $user->id;
        }

        return false;
    }

    /**
     * Determine if the nanny can reject a pending appointment
     * Only when status is pending and nanny is assigned
     */
    public function reject(User $user, BookingAppointment $appointment): bool
    {
        // Check base permission
        if (! $this->hasPermission($user, BookingAppointmentPermission::Reject->value)) {
            return false;
        }

        // Must be in pending status
        if ($appointment->status->value !== StatusEnum::PENDING->value) {
            return false;
        }

        // Must have a nanny assigned
        if ($appointment->nanny_id === null) {
            return false;
        }

        // Admin can always reject
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Nanny can only reject their own assignments
        if ($user->hasRole(RoleEnum::NANNY->value)) {
            $appointment->loadMissing('nanny');

            return (int) $appointment->nanny->user_id === (int) $user->id;
        }

        return false;
    }

    /**
     * Determine if the user can unassign a nanny from a confirmed appointment
     * Only when status is confirmed
     */
    public function unassignNanny(User $user, BookingAppointment $appointment): bool
    {
        // Check base permission
        if (! $this->hasPermission($user, BookingAppointmentPermission::UnassignNanny->value)) {
            return false;
        }

        // Must be in confirmed status
        if ($appointment->status->value !== StatusEnum::CONFIRMED->value) {
            return false;
        }

        // Must have a nanny assigned
        if ($appointment->nanny_id === null) {
            return false;
        }

        // Admin can always unassign
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Tutor can unassign from their own bookings
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $bookingTutorUserId = $appointment->booking?->tutor?->user_id;

            return $bookingTutorUserId !== null && (int) $bookingTutorUserId === (int) $user->id;
        }

        // Nanny can unassign themselves
        if ($user->hasRole(RoleEnum::NANNY->value)) {
            $appointment->loadMissing('nanny');

            return (int) $appointment->nanny->user_id === (int) $user->id;
        }

        return false;
    }

    /**
     * Determine if the user can cancel an appointment
     * Cannot cancel if already completed
     */
    public function cancel(User $user, BookingAppointment $appointment): bool
    {
        // Check base permission
        if (! $this->hasPermission($user, BookingAppointmentPermission::Cancel->value)) {
            return false;
        }

        // Cannot cancel completed appointments
        if ($appointment->status->value === StatusEnum::COMPLETED->value) {
            return false;
        }

        // Admin can cancel any
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Tutor can cancel their own appointments
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $bookingTutorUserId = $appointment->booking?->tutor?->user_id;

            return $bookingTutorUserId !== null && (int) $bookingTutorUserId === (int) $user->id;
        }

        // Nanny can cancel appointments assigned to them
        if ($user->hasRole(RoleEnum::NANNY->value) && $appointment->nanny_id !== null) {
            $appointment->loadMissing('nanny');

            return (int) $appointment->nanny->user_id === (int) $user->id;
        }

        return false;
    }

    /**
     * Determine if the nanny can review the tutor
     * Only when status is completed and not already reviewed
     */
    public function reviewTutor(User $user, BookingAppointment $appointment): bool
    {
        // Check base permission
        if (! $this->hasPermission($user, BookingAppointmentPermission::ReviewTutor->value)) {
            return false;
        }

        // Must be completed
        if ($appointment->status->value !== StatusEnum::COMPLETED->value) {
            return false;
        }

        // Must not have already reviewed
        if ($appointment->reviewed_by_nanny) {
            return false;
        }

        // Admin can create reviews
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Nanny can review if they were assigned to this appointment
        if ($user->hasRole(RoleEnum::NANNY->value) && $appointment->nanny_id !== null) {
            $appointment->loadMissing('nanny');

            return (int) $appointment->nanny->user_id === (int) $user->id;
        }

        return false;
    }

    /**
     * Determine if the tutor can review the nanny
     * Only when status is completed and not already reviewed
     */
    public function reviewNanny(User $user, BookingAppointment $appointment): bool
    {
        // Check base permission
        if (! $this->hasPermission($user, BookingAppointmentPermission::ReviewNanny->value)) {
            return false;
        }

        // Must be completed
        if ($appointment->status->value !== StatusEnum::COMPLETED->value) {
            return false;
        }

        // Must not have already reviewed
        if ($appointment->reviewed_by_tutor) {
            return false;
        }

        // Admin can create reviews
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Tutor can review their own appointments
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $bookingTutorUserId = $appointment->booking?->tutor?->user_id;

            return $bookingTutorUserId !== null && (int) $bookingTutorUserId === (int) $user->id;
        }

        return false;
    }

    private function hasPermission(User $user, string $permission): bool
    {
        $userRoles = $user->roles->pluck('name')->toArray();

        foreach ($userRoles as $roleName) {
            $roleEnum = $this->getRoleEnum($roleName);
            if ($roleEnum && BookingAppointmentPermission::allows($permission, $roleEnum)) {
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
