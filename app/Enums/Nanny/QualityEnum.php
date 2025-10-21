<?php

namespace App\Enums\Nanny;

enum QualityEnum: string
{
    case RESPONSABLE = 'responsable';
    case PACIENTE = 'paciente';
    case CREATIVA = 'creativa';
    case PUNTUAL = 'puntual';
    case CARIÑOSA = 'carinosa';
    case ORGANIZADA = 'organizada';
    case COMUNICATIVA = 'comunicativa';
    case EXTROVERTIDA = 'extrovertida';
    case LECTORA = 'lectora';
    case EMPATICA = 'empatica';
    case CUIDADOSA = 'cuidadosa';
    case HONESTA = 'honesta';
    case ATENTA = 'atenta';
    case ASERTIVA = 'asertiva';
    case RESPETUOSA = 'respetuosa';
    case DINAMICA = 'dinamica';
    case PROACTIVA = 'proactiva';
    case DISCRETA = 'discreta';
    case OBSERVADORA = 'observadora';
    case RESOLUTIVA = 'resolutiva';
    case COLABORATIVA = 'colaborativa';
    case FLEXIBLE = 'flexible';
    case LUDICA = 'ludica';
    case ADAPTABLE = 'adaptable';
    case PERSEVERANTE = 'perseverante';
    case TOLERANTE = 'tolerante';
    case SEGURA = 'segura';
    case BILINGUE = 'bilingue';

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
            self::EMPATICA => 'Empática',
            self::CUIDADOSA => 'Cuidadosa',
            self::HONESTA => 'Honesta',
            self::ATENTA => 'Atenta',
            self::ASERTIVA => 'Asertiva',
            self::RESPETUOSA => 'Respetuosa',
            self::DINAMICA => 'Dinámica',
            self::PROACTIVA => 'Proactiva',
            self::DISCRETA => 'Discreta',
            self::OBSERVADORA => 'Observadora',
            self::RESOLUTIVA => 'Resolutiva',
            self::COLABORATIVA => 'Colaborativa',
            self::FLEXIBLE => 'Flexible',
            self::LUDICA => 'Lúdica',
            self::ADAPTABLE => 'Adaptable',
            self::PERSEVERANTE => 'Perseverante',
            self::TOLERANTE => 'Tolerante',
            self::SEGURA => 'Segura',
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
