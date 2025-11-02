<?php

namespace App\Enums\Address;

enum TypeEnum: string
{
    case FRACCIONAMIENTO = 'fraccionamiento';
    case CASA = 'casa';
    case EDIFICIO = 'edificio';
    case DEPARTAMENTO = 'departamento';
    case DUPLEX = 'dúplex';
    case LOCAL = 'local';
    case PARQUE_DIVERSIONES = 'parque_de_diversiones';
    case HOTEL = 'hotel';
    case SALON_FIESTAS = 'salon_de_fiestas';
    case CONDOMINIO = 'condominio';
    case OTRO = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::FRACCIONAMIENTO => 'Fraccionamiento',
            self::CASA => 'Casa',
            self::EDIFICIO => 'Edificio',
            self::DEPARTAMENTO => 'Departamento',
            self::DUPLEX => 'Dúplex',
            self::LOCAL => 'Local',
            self::PARQUE_DIVERSIONES => 'Parque de diversiones',
            self::HOTEL => 'Hotel',
            self::SALON_FIESTAS => 'Salón de fiestas',
            self::CONDOMINIO => 'Condominio',
            self::OTRO => 'Otro',
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
