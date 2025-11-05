<?php

namespace App\Enums\Permissions;

use App\Concerns\HasPermissions;
use App\Enums\User\RoleEnum;

enum BookingAppointmentPermission: string
{
    use HasPermissions;

    case ViewAny = 'booking_appointment.viewAny';
    case View = 'booking_appointment.view';
    case ChooseNanny = 'booking_appointment.choose_nanny';
    case AssignNanny = 'booking_appointment.assign_nanny';
    case UpdateDates = 'booking_appointment.update_dates';
    case UpdateAddress = 'booking_appointment.update_address';
    case UpdateChildren = 'booking_appointment.update_children';
    case Accept = 'booking_appointment.accept';
    case Reject = 'booking_appointment.reject';
    case UnassignNanny = 'booking_appointment.unassign_nanny';
    case Cancel = 'booking_appointment.cancel';
    case ReviewTutor = 'booking_appointment.review_tutor';
    case ReviewNanny = 'booking_appointment.review_nanny';
    case Delete = 'booking_appointment.delete';

    public static function map(): array
    {
        return [
            self::ViewAny->value => [RoleEnum::ADMIN, RoleEnum::NANNY],
            self::View->value => [RoleEnum::ADMIN, RoleEnum::TUTOR, RoleEnum::NANNY],
            self::ChooseNanny->value => [RoleEnum::ADMIN, RoleEnum::TUTOR],
            self::AssignNanny->value => [RoleEnum::ADMIN, RoleEnum::TUTOR],
            self::UpdateDates->value => [RoleEnum::ADMIN, RoleEnum::TUTOR],
            self::UpdateAddress->value => [RoleEnum::ADMIN, RoleEnum::TUTOR],
            self::UpdateChildren->value => [RoleEnum::ADMIN, RoleEnum::TUTOR],
            self::Accept->value => [RoleEnum::ADMIN, RoleEnum::NANNY],
            self::Reject->value => [RoleEnum::ADMIN, RoleEnum::NANNY],
            self::UnassignNanny->value => [RoleEnum::ADMIN, RoleEnum::TUTOR, RoleEnum::NANNY],
            self::Cancel->value => [RoleEnum::ADMIN, RoleEnum::TUTOR, RoleEnum::NANNY],
            self::ReviewTutor->value => [RoleEnum::ADMIN, RoleEnum::NANNY],
            self::ReviewNanny->value => [RoleEnum::ADMIN, RoleEnum::TUTOR],
            self::Delete->value => [RoleEnum::ADMIN, RoleEnum::TUTOR],
        ];
    }
}
