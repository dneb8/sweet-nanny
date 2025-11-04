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
        return $this->hasPermission($user, UserPermission::Delete->value);
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
