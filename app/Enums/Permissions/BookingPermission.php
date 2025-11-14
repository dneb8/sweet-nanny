<?php

namespace App\Enums\Permissions;

use App\Concerns\HasPermissions;
use App\Enums\User\RoleEnum;

enum BookingPermission: string
{
    use HasPermissions;

    case ViewAny = 'booking.viewAny';
    case View = 'booking.view';
    case Create = 'booking.create';
    case Update = 'booking.update';
    case Delete = 'booking.delete';

    public static function map(): array
    {
        return [
            self::ViewAny->value => [RoleEnum::ADMIN, RoleEnum::TUTOR],
            self::View->value => [RoleEnum::ADMIN, RoleEnum::TUTOR, RoleEnum::NANNY],
            self::Create->value => [RoleEnum::ADMIN, RoleEnum::TUTOR],
            self::Update->value => [RoleEnum::ADMIN, RoleEnum::TUTOR],
            self::Delete->value => [RoleEnum::ADMIN, RoleEnum::TUTOR],
        ];
    }
}
