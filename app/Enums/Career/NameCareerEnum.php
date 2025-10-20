<?php

namespace App\Enums\Career;

enum NameCareerEnum: string
{
    case PEDAGOGIA = 'PEDAGOGIA';
    case PSICOLOGIA = 'PSICOLOGIA';
    case ENFERMERIA = 'ENFERMERIA';
    case ARTES = 'ARTES';
    case EDUCACION_ESPECIAL = 'EDUCACION_ESPECIAL';
    case TRABAJO_SOCIAL = 'TRABAJO_SOCIAL'; 
    case PSICOPEDAGOGIA = 'PSICOPEDAGOGIA'; 
    case EDUCACION_INFANTIL = 'EDUCACION_INFANTIL'; 
    case NUTRICION = 'NUTRICION';

    public function label(): string
    {
        return match($this){
            self::PEDAGOGIA => 'Pedagogía',
            self::PSICOLOGIA => 'Psicología',
            self::ENFERMERIA => 'Enfermería',
            self::ARTES => 'Artes',
            self::EDUCACION_ESPECIAL => 'Educación especial',
            self::TRABAJO_SOCIAL => 'Trabajo social',
            self::PSICOPEDAGOGIA => 'Psicopedagogía',
            self::EDUCACION_INFANTIL => 'Educación infantil',
            self::NUTRICION => 'Nutrición',
        };
    }
}