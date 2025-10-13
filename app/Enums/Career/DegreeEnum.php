<?php

namespace App\Enums\Career;

// Importante: Debemos usar PHP 8.1+ Enums con string backing values
enum DegreeEnum: string
{
    // Valores que se guardarán en la base de datos (clave interna)
    case BACHILLERATO = 'bachillerato';
    case TECNICO = 'tecnico';
    case LICENCIATURA = 'licenciatura';
    case MAESTRIA = 'maestria';
    case DOCTORADO = 'doctorado';
    /**
     * Define la etiqueta amigable para mostrar en el frontend o UI.
     */
    public function label(): string
    {
        return match ($this) {
            static::BACHILLERATO => 'Bachillerato / Preparatoria',
            static::TECNICO      => 'Técnico / Carrera Técnica',
            static::LICENCIATURA => 'Licenciatura / Ingeniería',
            static::MAESTRIA     => 'Maestría / Especialidad',
            static::DOCTORADO    => 'Doctorado',
        };
    }
    
    /**
     * Devuelve el Enum como un array de { valor_db => Etiqueta_amigable }
     */
    public static function toArray(): array
    {
        $values = array_column(self::cases(), 'value');
        $labels = array_map(fn ($case) => $case->label(), self::cases());
        
        return array_combine($values, $labels);
    }
}
