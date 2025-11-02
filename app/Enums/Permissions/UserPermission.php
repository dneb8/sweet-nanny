<?php

namespace App\Enums\Permissions;

use App\Concerns\HasPermissions;
use App\Enums\User\RoleEnum;

enum UserPermission: string
{
    use HasPermissions;

    case Index = 'user.index';
    case Create = 'user.create';
    case Update = 'user.update';
    case Delete = 'user.delete';

    public static function map(): array
    {
        return [
            self::Index->value => [RoleEnum::ADMIN],
            self::Create->value => [RoleEnum::ADMIN],
            self::Update->value => [RoleEnum::ADMIN],
            self::Delete->value => [RoleEnum::ADMIN],
        ];
    }
}
