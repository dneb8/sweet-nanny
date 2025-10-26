<?php

namespace App\Enums\Nanny;

enum QualityEnum: string
{
    case EMPATICA = 'empatica';
    case CREATIVA = 'creativa';
    case PACIENTE = 'paciente';
    case CARIÑOSA = 'carinosa';
    case OBSERVADORA = 'observadora';
    case ASERTIVA = 'asertiva';
    case PROACTIVA = 'proactiva';
    case FLEXIBLE = 'flexible';
    case LUDICA = 'ludica';
    case BILINGUE = 'bilingue';

    public function label(): string
    {
        return match ($this) {
            self::EMPATICA => 'Empática',
            self::CREATIVA => 'Creativa',
            self::PACIENTE => 'Paciente',
            self::CARIÑOSA => 'Cariñosa',
            self::OBSERVADORA => 'Observadora',
            self::ASERTIVA => 'Asertiva',
            self::PROACTIVA => 'Proactiva',
            self::FLEXIBLE => 'Flexible',
            self::LUDICA => 'Lúdica',
            self::BILINGUE => 'Bilingüe',
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
