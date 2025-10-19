<?php

namespace App\Enums\Nanny;

enum QualityEnum: string
{
    case RESPONSABLE = 'Responsable';
    case PACIENTE = 'Paciente';
    case CREATIVA = 'Creativa';
    case PUNTUAL = 'Puntual';
    case CARIÑOSA = 'Cariñosa';
    case ORGANIZADA = 'Organizada';
    case COMUNICATIVA = 'Comunicativa';

    case EXTROVERTIDA = 'Extrovertida';

    case LECTORA = 'Lectora';

    public function label(): string
    {
        return match ($this) {
            self::RESPONSABLE => 'Responsable',
            self::PACIENTE => 'Paciente',
            self::CREATIVA => 'Creativa',
            self::PUNTUAL => 'Puntual',
            self::CARIÑOSA => 'Cariñosa',
            self::ORGANIZADA => 'Organizada',
            self::COMUNICATIVA => 'Comunicativa',
            self::EXTROVERTIDA => 'Extrovertida',
            self::LECTORA => 'Lectora',
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
