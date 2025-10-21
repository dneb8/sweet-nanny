<?php

namespace App\Enums\Career;

enum NameCareerEnum: string
{
    case PEDAGOGIA = 'pedagogia';
    case PSICOLOGIA = 'psicologia';
    case ENFERMERIA = 'enfermeria';
    case ARTES = 'artes';
    case EDUCACION_ESPECIAL = 'educacion_especial';
    case TRABAJO_SOCIAL = 'trabajo_social'; 
    case PSICOPEDAGOGIA = 'psicopedagogia'; 
    case EDUCACION_INFANTIL = 'educacion_infantil'; 
    case NUTRICION = 'nutricion';
    case EDUCACION_PREESCOLAR = 'educacion_preescolar';
    case PSICOLOGIA_INFANTIL = 'psicologia_infantil';
    case PUERICULTURA = 'puericultura';
    case TERAPIA_OCUPACIONAL = 'terapia_ocupacional';
    case ESTIMULACION_TEMPRANA = 'estimulacion_temprana';
    case ENFERMERIA_PEDIATRICA = 'enfermeria_pediatrica';

    public function label(): string
    {
        return match($this){
            self::PEDAGOGIA => 'Pedagogía',
            self::PSICOLOGIA => 'Psicología',
            self::ENFERMERIA => 'Enfermería',
            self::ARTES => 'Artes',
            self::EDUCACION_ESPECIAL => 'Educación Especial',
            self::TRABAJO_SOCIAL => 'Trabajo Social',
            self::PSICOPEDAGOGIA => 'Psicopedagogía',
            self::EDUCACION_INFANTIL => 'Educación Infantil',
            self::NUTRICION => 'Nutrición',
            self::EDUCACION_PREESCOLAR => 'Educación Preescolar',
            self::PSICOLOGIA_INFANTIL => 'Psicología Infantil',
            self::PUERICULTURA => 'Puericultura',
            self::TERAPIA_OCUPACIONAL => 'Terapia Ocupacional',
            self::ESTIMULACION_TEMPRANA => 'Estimulación Temprana',
            self::ENFERMERIA_PEDIATRICA => 'Enfermería Pediátrica',
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