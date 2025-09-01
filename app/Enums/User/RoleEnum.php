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
            static::ADMIN => 'Administrator',
            static::TUTOR => 'Tutor',
            static::NANNY => 'Nanny'
        };
    }
}
