<?php

namespace App\Policies;

use App\Enums\Booking\StatusEnum;
use App\Enums\Permissions\BookingAppointmentPermission;
use App\Enums\User\RoleEnum;
use App\Models\BookingAppointment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingAppointmentPolicy
{
    /**
     * Ver cualquier cita
     */
    public function viewAny(User $user): Response
    {
        return $this->hasPermission($user, BookingAppointmentPermission::ViewAny->value)
            ? Response::allow()
            : Response::deny('No cuentas con el permiso para ver citas.');
    }

    /**
     * Eliminar una cita
     */
    public function delete(User $user, BookingAppointment $appointment): Response
    {
        return $this->hasPermission($user, BookingAppointmentPermission::Delete->value)
            ? Response::allow()
            : Response::deny('No cuentas con el permiso para eliminar esta cita.');
    }

    /**
     * Elegir/buscar niñeras (solo en draft y sin niñera asignada)
     */
    public function chooseNanny(User $user, BookingAppointment $appointment): Response
    {
        if (! $this->hasPermission($user, BookingAppointmentPermission::ChooseNanny->value)) {
            return Response::deny('No cuentas con el permiso para elegir niñera.');
        }

        if ($appointment->status->value !== StatusEnum::DRAFT->value) {
            return Response::deny('Solo se puede elegir niñera cuando la cita está en borrador.');
        }

        if ($appointment->nanny_id !== null) {
            return Response::deny('Esta cita ya tiene una niñera asignada.');
        }

        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return Response::allow();
        }

        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $ownerId = $appointment->booking?->tutor?->user_id;
            return ($ownerId !== null && (int)$ownerId === (int)$user->id)
                ? Response::allow()
                : Response::deny('Solo el tutor dueño del booking puede elegir niñera para esta cita.');
        }

        return Response::deny('Tu rol no está autorizado para esta acción.');
    }

    /**
     * Asignar niñera (solo en draft)
     */
    public function assignNanny(User $user, BookingAppointment $appointment): Response
    {
        if (! $this->hasPermission($user, BookingAppointmentPermission::AssignNanny->value)) {
            return Response::deny('No cuentas con el permiso para asignar niñera.');
        }

        if ($appointment->status->value !== StatusEnum::DRAFT->value) {
            return Response::deny('Solo se puede asignar niñera cuando la cita está en borrador.');
        }

        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return Response::allow();
        }

        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $ownerId = $appointment->booking?->tutor?->user_id;
            return ($ownerId !== null && (int)$ownerId === (int)$user->id)
                ? Response::allow()
                : Response::deny('Solo el tutor dueño del booking puede asignar niñera para esta cita.');
        }

        return Response::deny('Tu rol no está autorizado para esta acción.');
    }

    /**
     * Actualizar fechas (draft o pending). Si está pending, puede desasignar niñera.
     */
    public function updateDates(User $user, BookingAppointment $appointment): Response
    {
        if (! $this->hasPermission($user, BookingAppointmentPermission::UpdateDates->value)) {
            return Response::deny('No cuentas con el permiso para actualizar fechas.');
        }

        if (! in_array($appointment->status->value, [StatusEnum::DRAFT->value, StatusEnum::PENDING->value], true)) {
            return Response::deny('Solo se pueden actualizar fechas cuando la cita está en borrador o pendiente.');
        }

        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return Response::allow();
        }

        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $ownerId = $appointment->booking?->tutor?->user_id;
            return ($ownerId !== null && (int)$ownerId === (int)$user->id)
                ? Response::allow()
                : Response::deny('Solo el tutor dueño del booking puede actualizar fechas de esta cita.');
        }

        return Response::deny('Tu rol no está autorizado para esta acción.');
    }

    /**
     * Actualizar dirección (draft o pending). Si está pending, puede desasignar niñera.
     */
    public function updateAddress(User $user, BookingAppointment $appointment): Response
    {
        if (! $this->hasPermission($user, BookingAppointmentPermission::UpdateAddress->value)) {
            return Response::deny('No cuentas con el permiso para actualizar la dirección.');
        }

        if (! in_array($appointment->status->value, [StatusEnum::DRAFT->value, StatusEnum::PENDING->value], true)) {
            return Response::deny('Solo se puede actualizar la dirección cuando la cita está en borrador o pendiente.');
        }

        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return Response::allow();
        }

        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $ownerId = $appointment->booking?->tutor?->user_id;
            return ($ownerId !== null && (int)$ownerId === (int)$user->id)
                ? Response::allow()
                : Response::deny('Solo el tutor dueño del booking puede actualizar la dirección de esta cita.');
        }

        return Response::deny('Tu rol no está autorizado para esta acción.');
    }

    /**
     * Actualizar niños (draft o pending). Si está pending, puede desasignar niñera.
     */
    public function updateChildren(User $user, BookingAppointment $appointment): Response
    {
        if (! $this->hasPermission($user, BookingAppointmentPermission::UpdateChildren->value)) {
            return Response::deny('No cuentas con el permiso para actualizar los niños.');
        }

        if (! in_array($appointment->status->value, [StatusEnum::DRAFT->value, StatusEnum::PENDING->value], true)) {
            return Response::deny('Solo se pueden actualizar los niños cuando la cita está en borrador o pendiente.');
        }

        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return Response::allow();
        }

        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $ownerId = $appointment->booking?->tutor?->user_id;
            return ($ownerId !== null && (int)$ownerId === (int)$user->id)
                ? Response::allow()
                : Response::deny('Solo el tutor dueño del booking puede actualizar los niños de esta cita.');
        }

        return Response::deny('Tu rol no está autorizado para esta acción.');
    }

    /**
     * Aceptar cita (nanny asignada y status pending)
     */
    public function accept(User $user, BookingAppointment $appointment): Response
    {
        if (! $this->hasPermission($user, BookingAppointmentPermission::Accept->value)) {
            return Response::deny('No cuentas con el permiso para aceptar la cita.');
        }

        if ($appointment->status->value !== StatusEnum::PENDING->value) {
            return Response::deny('Solo se puede aceptar cuando la cita está pendiente.');
        }

        if ($appointment->nanny_id === null) {
            return Response::deny('No hay niñera asignada a esta cita.');
        }

        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return Response::allow();
        }

        if ($user->hasRole(RoleEnum::NANNY->value)) {
            $appointment->loadMissing('nanny');
            return ((int)$appointment->nanny->user_id === (int)$user->id)
                ? Response::allow()
                : Response::deny('Solo la niñera asignada puede aceptar esta cita.');
        }

        return Response::deny('Tu rol no está autorizado para esta acción.');
    }

    /**
     * Rechazar cita (nanny asignada y status pending)
     */
    public function reject(User $user, BookingAppointment $appointment): Response
    {
        if (! $this->hasPermission($user, BookingAppointmentPermission::Reject->value)) {
            return Response::deny('No cuentas con el permiso para rechazar la cita.');
        }

        if ($appointment->status->value !== StatusEnum::PENDING->value) {
            return Response::deny('Solo se puede rechazar cuando la cita está pendiente.');
        }

        if ($appointment->nanny_id === null) {
            return Response::deny('No hay niñera asignada a esta cita.');
        }

        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return Response::allow();
        }

        if ($user->hasRole(RoleEnum::NANNY->value)) {
            $appointment->loadMissing('nanny');
            return ((int)$appointment->nanny->user_id === (int)$user->id)
                ? Response::allow()
                : Response::deny('Solo la niñera asignada puede rechazar esta cita.');
        }

        return Response::deny('Tu rol no está autorizado para esta acción.');
    }

    /**
     * Desasignar niñera (solo confirmed)
     */
    public function unassignNanny(User $user, BookingAppointment $appointment): Response
    {
        if (! $this->hasPermission($user, BookingAppointmentPermission::UnassignNanny->value)) {
            return Response::deny('No cuentas con el permiso para desasignar la niñera.');
        }

        if ($appointment->status->value !== StatusEnum::CONFIRMED->value) {
            return Response::deny('Solo se puede desasignar la niñera cuando la cita está confirmada.');
        }

        if ($appointment->nanny_id === null) {
            return Response::deny('Esta cita no tiene una niñera asignada.');
        }

        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return Response::allow();
        }

        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $ownerId = $appointment->booking?->tutor?->user_id;
            return ($ownerId !== null && (int)$ownerId === (int)$user->id)
                ? Response::allow()
                : Response::deny('Solo el tutor dueño del booking puede desasignar la niñera.');
        }

        if ($user->hasRole(RoleEnum::NANNY->value)) {
            $appointment->loadMissing('nanny');
            return ((int)$appointment->nanny->user_id === (int)$user->id)
                ? Response::allow()
                : Response::deny('Solo la niñera asignada puede desasignarse.');
        }

        return Response::deny('Tu rol no está autorizado para esta acción.');
    }

    /**
     * Cancelar cita (no se puede si está completada)
     */
    public function cancel(User $user, BookingAppointment $appointment): Response
    {
        if (! $this->hasPermission($user, BookingAppointmentPermission::Cancel->value)) {
            return Response::deny('No cuentas con el permiso para cancelar la cita.');
        }

        if ($appointment->status->value === StatusEnum::COMPLETED->value) {
            return Response::deny('No se puede cancelar una cita ya completada.');
        }

        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return Response::allow();
        }

        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $ownerId = $appointment->booking?->tutor?->user_id;
            return ($ownerId !== null && (int)$ownerId === (int)$user->id)
                ? Response::allow()
                : Response::deny('Solo el tutor dueño del booking puede cancelar esta cita.');
        }

        if ($user->hasRole(RoleEnum::NANNY->value) && $appointment->nanny_id !== null) {
            $appointment->loadMissing('nanny');
            return ((int)$appointment->nanny->user_id === (int)$user->id)
                ? Response::allow()
                : Response::deny('Solo la niñera asignada puede cancelar esta cita.');
        }

        return Response::deny('Tu rol no está autorizado para esta acción.');
    }

    /**
     * Review del tutor por la niñera (solo completed y no revisado aún)
     */
    public function reviewTutor(User $user, BookingAppointment $appointment): Response
    {
        if (! $this->hasPermission($user, BookingAppointmentPermission::ReviewTutor->value)) {
            return Response::deny('No cuentas con el permiso para dejar reseña del tutor.');
        }

        if ($appointment->status->value !== StatusEnum::COMPLETED->value) {
            return Response::deny('Solo se puede reseñar cuando la cita está completada.');
        }

        if ($appointment->reviewed_by_nanny) {
            return Response::deny('Esta cita ya fue reseñada por la niñera.');
        }

        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return Response::allow();
        }

        if ($user->hasRole(RoleEnum::NANNY->value) && $appointment->nanny_id !== null) {
            $appointment->loadMissing('nanny');
            return ((int)$appointment->nanny->user_id === (int)$user->id)
                ? Response::allow()
                : Response::deny('Solo la niñera asignada puede reseñar al tutor.');
        }

        return Response::deny('Tu rol no está autorizado para esta acción.');
    }

    /**
     * Review de la niñera por el tutor (solo completed y no revisado aún)
     */
    public function reviewNanny(User $user, BookingAppointment $appointment): Response
    {
        if (! $this->hasPermission($user, BookingAppointmentPermission::ReviewNanny->value)) {
            return Response::deny('No cuentas con el permiso para dejar reseña de la niñera.');
        }

        if ($appointment->status->value !== StatusEnum::COMPLETED->value) {
            return Response::deny('Solo se puede reseñar cuando la cita está completada.');
        }

        if ($appointment->reviewed_by_tutor) {
            return Response::deny('Esta cita ya fue reseñada por el tutor.');
        }

        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return Response::allow();
        }

        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $ownerId = $appointment->booking?->tutor?->user_id;
            return ($ownerId !== null && (int)$ownerId === (int)$user->id)
                ? Response::allow()
                : Response::deny('Solo el tutor dueño del booking puede reseñar a la niñera.');
        }

        return Response::deny('Tu rol no está autorizado para esta acción.');
    }

    /* ------------------------- Helpers ------------------------- */

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
