<?php

namespace App\Enums\Permissions;

use App\Concerns\HasPermissions;
use App\Enums\User\RoleEnum;

enum BookingAppointmentPermission: string
{
    use HasPermissions;

    case ViewAny        = 'booking_appointment.view_any';
    case View           = 'booking_appointment.view';
    case ChooseNanny    = 'booking_appointment.choose_nanny';
    case AssignNanny    = 'booking_appointment.assign_nanny';
    case Cancel         = 'booking_appointment.cancel';
    case UnassignNanny  = 'booking_appointment.unassign_nanny';
    case Accept         = 'booking_appointment.accept';
    case Reject         = 'booking_appointment.reject';
    case UpdateDates    = 'booking_appointment.update_dates';
    case UpdateAddress  = 'booking_appointment.update_address';
    case UpdateChildren = 'booking_appointment.update_children';
    case Delete         = 'booking_appointment.delete';
public static function map(): array
    {
        return [
            self::ViewAny->value        => [RoleEnum::ADMIN, RoleEnum::TUTOR, RoleEnum::NANNY],
            self::View->value           => [RoleEnum::ADMIN, RoleEnum::TUTOR, RoleEnum::NANNY],

            self::ChooseNanny->value    => [RoleEnum::ADMIN, RoleEnum::TUTOR],
            self::AssignNanny->value    => [RoleEnum::ADMIN, RoleEnum::TUTOR],

            self::Cancel->value         => [RoleEnum::ADMIN, RoleEnum::TUTOR], 
            self::UnassignNanny->value  => [RoleEnum::ADMIN, RoleEnum::TUTOR, RoleEnum::NANNY],

            self::Accept->value         => [RoleEnum::NANNY],
            self::Reject->value         => [RoleEnum::NANNY],

            self::UpdateDates->value    => [RoleEnum::ADMIN, RoleEnum::TUTOR],
            self::UpdateAddress->value  => [RoleEnum::ADMIN, RoleEnum::TUTOR],
            self::UpdateChildren->value => [RoleEnum::ADMIN, RoleEnum::TUTOR],

            self::Delete->value         => [RoleEnum::ADMIN],
        ];
    }
}
