<?php

namespace App\Enums\Career;

enum DegreeEnum: string
{
    case BACHILLERATO = 'bachillerato';
    case TECNICO = 'tecnico';
    case LICENCIATURA = 'licenciatura';
    case MAESTRIA = 'maestria';
    case DOCTORADO = 'doctorado';

    public function label(): string
    {
        return match ($this) {
            static::BACHILLERATO => 'Bachillerato',
            static::TECNICO      => 'Carrera Técnica',
            static::LICENCIATURA => 'Licenciatura',
            static::MAESTRIA     => 'Maestría',
            static::DOCTORADO    => 'Doctorado',
        };
    }

    public static function toArray(): array
    {
        $values = array_column(self::cases(), 'value');
        $labels = array_map(fn ($case) => $case->label(), self::cases());
        
        return array_combine($values, $labels);
    }
}
