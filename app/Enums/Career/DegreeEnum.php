<?php

namespace App\Enums\Career;

enum DegreeEnum: string
{
    case BACHILLERATO = 'Bachillerato';
    case LICENCIATURA = 'Licenciatura';
    case MAESTRIA = 'Maestría';
    case DOCTORADO = 'Doctorado';
    case TECNICO = 'Técnico';

    public function label(): string
    {
        return match ($this) {
            self::BACHILLERATO => 'Bachillerato',
            self::LICENCIATURA => 'Licenciatura',
            self::MAESTRIA => 'Maestría',
            self::DOCTORADO => 'Doctorado',
            self::TECNICO => 'Técnico',
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
