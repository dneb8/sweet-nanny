<?php

namespace App\Enums\Career;

enum DegreeEnum: string
{
    case LICENCIATURA = 'Licenciatura';
    case MAESTRIA = 'Maestría';
    case DOCTORADO = 'Doctorado';
    case TECNICO = 'Técnico';
    case DIPLOMADO = 'Diplomado';
    case CERTIFICACION = 'Certificación';

    public function label(): string
    {
        return match ($this) {
            self::LICENCIATURA => 'Licenciatura',
            self::MAESTRIA => 'Maestría',
            self::DOCTORADO => 'Doctorado',
            self::TECNICO => 'Técnico',
            self::DIPLOMADO => 'Diplomado',
            self::CERTIFICACION => 'Certificación',
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
