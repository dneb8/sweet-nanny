<?php

namespace App\Enums\Career;

enum NameCareerEnum: string
{
    case PEDAGOGIA = 'pedagogia';
    case PSICOLOGIA = 'psicologia';
    case ENFERMERIA = 'enfermeria';
    case DOCENCIA = 'docencia';
    case NUTRICION = 'nutricion';
    case TRABAJO_SOCIAL = 'trabajo_social';
    case PSICOPEDAGOGIA = 'psicopedagogia';
    case TERAPIA_PSICOMOTRIZ = 'terapia_psicomotriz';
    case PEDIATRIA = 'pediatria';
    case ARTES_ESCENICAS_DANZA = 'artes_escenicas_danza';

    public function label(): string
    {
        return match($this){
            self::PEDAGOGIA => 'Pedagogía',
            self::PSICOLOGIA => 'Psicología',
            self::ENFERMERIA => 'Enfermería',
            self::DOCENCIA => 'Docencia',
            self::NUTRICION => 'Nutrición',
            self::TRABAJO_SOCIAL => 'Trabajo Social',
            self::PSICOPEDAGOGIA => 'Psicopedagogía',
            self::TERAPIA_PSICOMOTRIZ => 'Terapia Psicomotriz',
            self::PEDIATRIA => 'Pediatría',
            self::ARTES_ESCENICAS_DANZA => 'Artes Escénicas / Danza',
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
