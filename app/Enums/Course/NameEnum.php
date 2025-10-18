<?php

namespace App\Enums\Course;

enum NameEnum: string
{
    case PRIMEROS_AUXILIOS = 'Primeros Auxilios';
    case CUIDADO_INFANTIL = 'Cuidado Infantil';
    case NUTRICION_INFANTIL = 'Nutrición Infantil';
    case DESARROLLO_INFANTIL = 'Desarrollo Infantil';
    case PSICOLOGIA_INFANTIL = 'Psicología Infantil';
    case PEDAGOGIA = 'Pedagogía';
    case EDUCACION_ESPECIAL = 'Educación Especial';
    case LACTANCIA_MATERNA = 'Lactancia Materna';
    case ESTIMULACION_TEMPRANA = 'Estimulación Temprana';
    case MANEJO_CONDUCTUAL = 'Manejo Conductual';

    public function label(): string
    {
        return match ($this) {
            self::PRIMEROS_AUXILIOS => 'Primeros Auxilios',
            self::CUIDADO_INFANTIL => 'Cuidado Infantil',
            self::NUTRICION_INFANTIL => 'Nutrición Infantil',
            self::DESARROLLO_INFANTIL => 'Desarrollo Infantil',
            self::PSICOLOGIA_INFANTIL => 'Psicología Infantil',
            self::PEDAGOGIA => 'Pedagogía',
            self::EDUCACION_ESPECIAL => 'Educación Especial',
            self::LACTANCIA_MATERNA => 'Lactancia Materna',
            self::ESTIMULACION_TEMPRANA => 'Estimulación Temprana',
            self::MANEJO_CONDUCTUAL => 'Manejo Conductual',
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
