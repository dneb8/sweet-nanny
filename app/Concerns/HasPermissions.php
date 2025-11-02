<?php

namespace App\Concerns;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission as PermissionModel;
use StringBackedEnum;

/**
 * @mixin StringBackedEnum
 */
trait HasPermissions
{
    /**
     * {@inheritDoc}
     */
    final public static function all(): Collection
    {
        return collect(self::cases());
    }

    final public function model(string $guardName = null): PermissionModel
    {
        return PermissionModel::findByName($this->value);
    }

    final public function trans(string $locale = null): string
    {
        return trans("permissions.$this->value", locale: $locale);
    }

    /**
     * Check if a given permission is allowed for a specific role
     *
     * @param string $permission The permission name to check
     * @param mixed $role The role enum to check against
     * @return bool
     */
    final public static function allows(string $permission, mixed $role): bool
    {
        $map = static::map();
        $allowedRoles = $map[$permission] ?? [];

        foreach ($allowedRoles as $allowedRole) {
            if ($allowedRole === $role || $allowedRole->value === $role->value) {
                return true;
            }
        }

        return false;
    }
}
