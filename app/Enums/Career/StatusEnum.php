<?php

namespace App\Enums\Career;

enum StatusEnum: string
{
    // Valores que se guardarán en la base de datos (clave interna)
    case EN_CURSO = 'en_curso';
    case FINALIZADO = 'finalizado';
    case TITULADO = 'titulado';
    case TRUNCA = 'trunca';

    /**
     * Define la etiqueta amigable para mostrar en el frontend o UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::EN_CURSO => 'En Curso',
            self::FINALIZADO => 'Finalizado (Egresado)',
            self::TITULADO => 'Titulado(a)',
            self::TRUNCA => 'Trunca (Abandonada)',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'label', 'value');
    }
}
