<?php

namespace App\Enums\Course;

enum NameEnum: string
{
    case PRIMEROS_AUXILIOS = 'primeros_auxilios';
    case CUIDADO_INFANTIL = 'cuidado_infantil';
    case DESARROLLO_INFANTIL = 'desarrollo_infantil';
    case NUTRICION_Y_ALIMENTACION = 'nutricion_y_alimentacion';
    case EDUCACION_Y_APRENDIZAJE = 'educacion_y_aprendizaje';
    case PSICOLOGIA_INFANTIL = 'psicologia_infantil';
    case DISCIPLINA_Y_COMPORTAMIENTO = 'disciplina_y_comportamiento';
    case LACTANCIA_Y_CUIDADO_BEBES = 'lactancia_y_cuidado_bebes';
    case INCLUSION_Y_DIVERSIDAD = 'inclusion_y_diversidad';
    case COMUNICACION_Y_LENGUAJE = 'comunicacion_y_lenguaje';

    public function label(): string
    {
        return match ($this) {
            self::PRIMEROS_AUXILIOS => 'Primeros Auxilios',
            self::CUIDADO_INFANTIL => 'Cuidado Infantil',
            self::DESARROLLO_INFANTIL => 'Desarrollo Infantil',
            self::NUTRICION_Y_ALIMENTACION => 'Nutrición y Alimentación',
            self::EDUCACION_Y_APRENDIZAJE => 'Educación y Aprendizaje',
            self::PSICOLOGIA_INFANTIL => 'Psicología Infantil',
            self::DISCIPLINA_Y_COMPORTAMIENTO => 'Disciplina y Comportamiento',
            self::LACTANCIA_Y_CUIDADO_BEBES => 'Lactancia y Cuidado de Bebés',
            self::INCLUSION_Y_DIVERSIDAD => 'Inclusión y Diversidad',
            self::COMUNICACION_Y_LENGUAJE => 'Comunicación y Lenguaje',
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
