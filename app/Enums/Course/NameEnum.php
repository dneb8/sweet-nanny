<?php

namespace App\Enums\Course;

enum NameEnum: string
{
    case PRIMEROS_AUXILIOS = 'Primeros Auxilios';
    case RCP_INFANTIL = 'RCP Infantil';
    case DESARROLLO_INFANTIL = 'Desarrollo Infantil';
    case PSICOLOGIA_INFANTIL = 'Psicología Infantil';
    case EDUCACION_PREESCOLAR = 'Educación Preescolar';
    case NUTRICION_INFANTIL = 'Nutrición Infantil';
    case CUIDADO_BEBE = 'Cuidado del Bebé';
    case MANEJO_CONDUCTA = 'Manejo de Conducta';

    public function label(): string
    {
        return $this->value;
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
