<?php

namespace App\Policies;

use App\Enums\Permissions\UserPermission;
use App\Enums\User\RoleEnum;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, UserPermission::Index->value);
    }

    /**
     * Determine if the user can view a specific user.
     */
    public function view(User $user, User $model): bool
    {
        // Admin can view any user
        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        // Nanny and Tutor can only view their own profile
        if ($user->hasRole(RoleEnum::NANNY->value) || $user->hasRole(RoleEnum::TUTOR->value)) {
            return $user->id === $model->id;
        }

        return false;
    }

    /**
     * Determine if the user can create users.
     */
    public function create(User $user): bool
    {
        return $this->hasPermission($user, UserPermission::Create->value);
    }

    /**
     * Determine if the user can update the given user.
     */
    public function update(User $user, User $model): bool
    {
        return $this->hasPermission($user, UserPermission::Update->value);
    }

    /**
     * Determine if the user can delete the given user.
     */
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

    /**
     * Check if the user has the required permission based on their roles.
     */
    private function hasPermission(User $user, string $permission): bool
    {
        // Get user's role(s) from Spatie
        $userRoles = $user->roles->pluck('name')->toArray();

        // Check if any of the user's roles has this permission
        foreach ($userRoles as $roleName) {
            $roleEnum = $this->getRoleEnum($roleName);
            if ($roleEnum && UserPermission::allows($permission, $roleEnum)) {
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
