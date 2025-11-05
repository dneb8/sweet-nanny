<?php

namespace App\Enums\Permissions;

use App\Concerns\HasPermissions;
use App\Enums\User\RoleEnum;

enum UserPermission: string
{
    use HasPermissions;

    case ViewAny = 'user.viewAny';
    case View    = 'user.view';
    case Create  = 'user.create';
    case Update  = 'user.update';
    case Delete  = 'user.delete';

    public static function map(): array
    {
        return [
            self::ViewAny->value => [RoleEnum::ADMIN],
            self::View->value    => [RoleEnum::ADMIN, RoleEnum::TUTOR, RoleEnum::NANNY],
            self::Create->value  => [RoleEnum::ADMIN],
            self::Update->value  => [RoleEnum::ADMIN],
            self::Delete->value  => [RoleEnum::ADMIN],
        ];
    }
}
