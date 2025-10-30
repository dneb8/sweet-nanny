<?php

namespace App\Enums\User;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case TUTOR = 'tutor';
    case NANNY = 'nanny';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::TUTOR => 'Tutor',
            self::NANNY => 'Nanny'
        };
    }
}
