<?php

namespace App\Enums\Children;

enum KinkshipEnum: string
{
    case HIJO     = 'hijo';
    case SOBRINO  = 'sobrino';
    case PRIMO    = 'primo';
    case HERMANO  = 'hermano';
    case OTRO     = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::HIJO     => 'Hijo(a)',
            self::SOBRINO  => 'Sobrino(a)',
            self::PRIMO    => 'Primo(a)',
            self::HERMANO  => 'Hermano(a)',
            self::OTRO     => 'Otro',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return array_combine(
            self::values(),
            array_map(fn ($case) => $case->label(), self::cases())
        );
    }
}
