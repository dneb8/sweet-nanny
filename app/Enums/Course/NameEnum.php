<?php

namespace App\Enums\Course;

enum NameEnum: string
{
    case PRIMEROS_AUXILIOS = 'primeros_auxilios';
    case RCP_PEDIATRICO = 'rcp_pediatrico';
    case ALIMENTACION_COMPLEMENTARIA = 'alimentacion_complementaria';
    case DISCIPLINA_POSITIVA = 'disciplina_positiva';
    case AUTISMO_TEA = 'autismo_tea';
    case LECTOESCRITURA = 'lectoescritura';
    case CUIDADO_RECIEN_NACIDO = 'cuidado_recien_nacido';
    case ESTIMULACION_MOTRIZ = 'estimulacion_motriz';
    case CUIDADO_INFANTIL = 'cuidado_infantil';
    case NUTRICION_INFANTIL = 'nutricion_infantil';
    case DESARROLLO_INFANTIL = 'desarrollo_infantil';
    case PSICOLOGIA_INFANTIL = 'psicologia_infantil';
    case PEDAGOGIA = 'pedagogia';
    case EDUCACION_ESPECIAL = 'educacion_especial';
    case LACTANCIA_MATERNA = 'lactancia_materna';
    case ESTIMULACION_TEMPRANA = 'estimulacion_temprana';
    case MANEJO_CONDUCTUAL = 'manejo_conductual';

    public function label(): string
    {
        return match ($this) {
            self::PRIMEROS_AUXILIOS => 'Primeros Auxilios',
            self::RCP_PEDIATRICO => 'RCP Pediátrico',
            self::ALIMENTACION_COMPLEMENTARIA => 'Alimentación Complementaria',
            self::DISCIPLINA_POSITIVA => 'Disciplina Positiva',
            self::AUTISMO_TEA => 'Autismo (TEA)',
            self::LECTOESCRITURA => 'Lectoescritura',
            self::CUIDADO_RECIEN_NACIDO => 'Cuidado de Recién Nacido',
            self::ESTIMULACION_MOTRIZ => 'Estimulación Motriz',
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
            array_map(fn ($case) => $case->label(), self::cases())
        );
    }
}
