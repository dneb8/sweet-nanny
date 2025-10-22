<?php

namespace App\Enums\Address;

enum ZoneEnum: string
{
    case GUADALAJARA = 'guadalajara';
    case ZAPOPAN = 'zapopan';
    case TLAQUEPAQUE = 'tlaquepaque';
    case TLAJOMULCO = 'tlajomulco';
    case TONALA = 'tonala';

    public function label(): string
    {
        return match ($this) {
            self::GUADALAJARA => 'Guadalajara',
            self::ZAPOPAN => 'Zapopan',
            self::TLAQUEPAQUE => 'Tlaquepaque',
            self::TLAJOMULCO => 'Tlajomulco',
            self::TONALA => 'Tonalá',
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
            array_map(fn($case) => $case->label(), self::cases())
        );
    }
}
