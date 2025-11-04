<?php

namespace App\Policies;

use App\Enums\Permissions\UserPermission;
use App\Enums\User\RoleEnum;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, UserPermission::ViewAny->value);
    }

    public function view(User $user, User $model): bool
    {
        // ADMIN puede ver a cualquiera
        if ($this->hasRole($user, RoleEnum::ADMIN)) {
            return true;
        }

        // Cualquier otro rol: solo su propio perfil
        if ($user->id === $model->id) {
            return true;
        }

        // Nadie más puede ver perfiles ajenos
        return false;
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, UserPermission::Create->value);
    }

    public function update(User $user, User $model): bool
    {
        return $this->hasPermission($user, UserPermission::Update->value);
    }

    public function delete(User $user, User $model): bool
    {
        // Admin has full delete permission via UserPermission
        if ($this->hasPermission($user, UserPermission::Delete->value)) {
            return true;
        }

        // Nanny and Tutor can delete their own account
        if ($user->hasRole(RoleEnum::NANNY->value) || $user->hasRole(RoleEnum::TUTOR->value)) {
            return $user->id === $model->id;
        }

        return false;
    }

    private function hasPermission(User $user, string $permission): bool
    {
        $userRoles = $user->roles->pluck('name')->toArray();

        foreach ($userRoles as $roleName) {
            $roleEnum = $this->getRoleEnum($roleName);
            if ($roleEnum && \App\Enums\Permissions\UserPermission::allows($permission, $roleEnum)) {
                return true;
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
