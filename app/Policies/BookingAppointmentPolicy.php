<?php

namespace App\Policies;

use App\Enums\Permissions\BookingAppointmentPermission;
use App\Enums\User\RoleEnum; // ajusta al namespace real
use App\Models\BookingAppointment;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class BookingAppointmentPolicy
{
    public function chooseNanny(User $user, BookingAppointment $appointment): Response
    {
        // --- Permiso objetivo
        $permName = BookingAppointmentPermission::ChooseNanny->value;

        // --- Construir lista de roles permitidos según el Enum::map()
        $map = BookingAppointmentPermission::map(); // ['perm' => [RoleEnum::ADMIN, RoleEnum::TUTOR], ...]
        $allowedRoleEnums = $map[$permName] ?? [];
        $allowedRoleNames = array_map(static fn($r) => $r->value, $allowedRoleEnums);

        // --- Datos actuales del usuario
        $userRoles = $user->getRoleNames()->toArray();                         // roles que Spatie VE
        $userPerms = $user->getAllPermissions()->pluck('name')->toArray();     // permisos efectivos
        $guard     = config('auth.defaults.guard');

        // --- Derivar por qué no hay match (si lo hubiera)
        $roleMatch = count(array_intersect(
            array_map('strtolower', $userRoles),
            array_map('strtolower', $allowedRoleNames)
        )) > 0;

        // --- DIAGNÓSTICO inicial
        Log::debug('BookingAppointmentPolicy.chooseNanny: diagnostic', [
            'user_id'               => $user->id,
            'user_email'            => $user->email,
            'guard'                 => $guard,
            'needed_perm'           => $permName,
            'allowed_roles_by_perm' => $allowedRoleNames,   // <- LO QUE PEDISTE: roles permitidos según el permiso
            'user_roles'            => $userRoles,          // <- roles que tiene el usuario
            'roles_intersection'    => array_values(array_intersect($userRoles, $allowedRoleNames)),
            'has_role_match'        => $roleMatch,          // true/false
            'user_perms'            => $userPerms,          // <- permisos efectivos
        ]);

        // 1) Verificar permiso (Spatie). Si falla, devolvemos mensaje super claro
        if ($user->cannot($permName)) {
            // ¿Por qué puede fallar? guard mismatch / permiso no asignado al rol / caché / teams
            $reason = sprintf(
                "Falta el permiso '%s'. Roles permitidos por el permiso: [%s]. Roles del usuario: [%s]. " .
                "Permisos efectivos del usuario: [%s]. Guard actual: '%s'. " .
                "Posibles causas: (a) el permiso no está asignado al rol en este guard, " .
                "(b) caché de Spatie desactualizada, (c) teams/tenancy activo y no asignado en el team actual.",
                $permName,
                implode(', ', $allowedRoleNames),
                implode(', ', $userRoles),
                implode(', ', $userPerms),
                $guard
            );

            Log::warning('chooseNanny DENY: missing permission', [
                'user_id'    => $user->id,
                'needed_perm'=> $permName,
                'guard'      => $guard,
                'allowed'    => $allowedRoleNames,
                'user_roles' => $userRoles,
                'user_perms' => $userPerms,
            ]);

            return Response::deny($reason);
        }

        // 2) ADMIN: acceso total
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            Log::debug('chooseNanny ALLOW: ADMIN override', [
                'user_id'    => $user->id,
                'user_roles' => $userRoles,
            ]);
            return Response::allow();
        }

        // 3) TUTOR: sólo si es dueño del booking
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $bookingTutorUserId = $appointment->booking?->tutor?->user_id;

            Log::debug('chooseNanny: tutor ownership check', [
                'user_id'              => $user->id,
                'appointment_id'       => $appointment->id,
                'booking_id'           => $appointment->booking?->id,
                'booking_tutor_userId' => $bookingTutorUserId,
            ]);

            if (is_null($bookingTutorUserId)) {
                return Response::deny('La cita no tiene tutor asociado (booking->tutor->user_id es null).');
            }

            if ((int)$bookingTutorUserId !== (int)$user->id) {
                return Response::deny(sprintf(
                    'No eres dueño de este booking. booking.tutor.user_id=%s, user.id=%s.',
                    $bookingTutorUserId,
                    $user->id
                ));
            }

            Log::debug('chooseNanny ALLOW: tutor is owner', [
                'user_id' => $user->id,
                'booking_tutor_userId' => $bookingTutorUserId,
            ]);
            return Response::allow();
        }

        // 4) Otros roles: negar con detalle de por qué no matchea
        Log::warning('chooseNanny DENY: role not allowed', [
            'user_id'    => $user->id,
            'user_roles' => $userRoles,
            'allowed'    => $allowedRoleNames,
        ]);

        return Response::deny(sprintf(
            'Tu rol no está autorizado. Roles permitidos: [%s]. Tus roles: [%s].',
            implode(', ', $allowedRoleNames),
            implode(', ', $userRoles)
        ));
    }

    /**
     * Determine if the user can cancel an appointment
     * Uses the same logic as chooseNanny for authorization
     */
    public function cancel(User $user, BookingAppointment $appointment): Response
    {
        // Reuse the same authorization logic as chooseNanny
        return $this->chooseNanny($user, $appointment);
    }

    /**
     * Determine if the user can update an appointment
     */
    public function update(User $user, BookingAppointment $appointment): Response
    {
        // Check status - only draft and pending can be edited
        if (!in_array($appointment->status, ['draft', 'pending'])) {
            return Response::deny('Solo se pueden editar citas en estado draft o pending.');
        }

        // Admin can edit any
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return Response::allow();
        }

        // Tutor can edit their own
        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            $appointment->loadMissing('booking.tutor');
            $bookingTutorUserId = $appointment->booking?->tutor?->user_id;

            if (is_null($bookingTutorUserId)) {
                return Response::deny('La cita no tiene tutor asociado.');
            }

            if ((int)$bookingTutorUserId !== (int)$user->id) {
                return Response::deny('No eres dueño de este booking.');
            }

            return Response::allow();
        }

        return Response::deny('No tienes permiso para editar esta cita.');
    }
}
