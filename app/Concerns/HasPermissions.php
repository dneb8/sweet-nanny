<?php

namespace App\Concerns;

trait HasPermissions
{
    /**
     * Get the permission map for this enum
     * Must be implemented by the enum using this trait
     *
     * @return array<string, array<\App\Enums\User\RoleEnum>>
     */
    abstract public static function map(): array;

    /**
     * Get all permission values for this enum
     *
     * @return array<string>
     */
    public static function permissions(): array
    {
        return array_keys(static::map());
    }

    /**
     * Get roles allowed for a specific permission
     *
     * @return array<\App\Enums\User\RoleEnum>
     */
    public static function rolesFor(string $permission): array
    {
        return static::map()[$permission] ?? [];
    }

    /**
     * Check if a role is allowed for a specific permission
     */
    public static function allows(string $permission, \App\Enums\User\RoleEnum $role): bool
    {
        $allowedRoles = static::rolesFor($permission);

        return in_array($role, $allowedRoles, true);
    }
}
