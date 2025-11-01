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
}
