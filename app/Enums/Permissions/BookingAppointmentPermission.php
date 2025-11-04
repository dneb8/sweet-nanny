<?php

namespace App\Enums\Permissions;

use App\Concerns\HasPermissions;
use App\Enums\User\RoleEnum;

enum BookingAppointmentPermission: string
{
    use HasPermissions;

    case ChooseNanny = 'booking_appointment.choose_nanny';

    public static function map(): array
    {
        return [
            self::ChooseNanny->value => [ RoleEnum::ADMIN, RoleEnum::TUTOR],
        ];
    }
}
