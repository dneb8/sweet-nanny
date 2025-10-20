<?php

namespace App\Enums\Career;

enum DegreeEnum: string
{
    case LICENCIATURA = 'licenciatura';
    case MAESTRIA = 'maestria';
    case DOCTORADO = 'doctorado';
    case TECNICO = 'tecnico';
    case DIPLOMADO = 'diplomado';
    case CERTIFICACION = 'certificacion';

    public function label(): string
    {
        return match ($this) {
            self::LICENCIATURA => 'Licenciatura',
            self::MAESTRIA     => 'Maestría',
            self::DOCTORADO    => 'Doctorado',
            self::TECNICO      => 'Técnico',
            self::DIPLOMADO    => 'Diplomado',
            self::CERTIFICACION=> 'Certificación',
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
            array_map(fn(self $case) => $case->label(), self::cases())
        );
    }
}
