<?php

namespace App\Enums\Tutor;

enum VerificationStatusEnum: string
{
    case PENDIENTE = 'Pendiente';
    case VERIFICADO = 'Verificado';
    case DENEGADO = 'Denegado';

    /**
     * Devuelve la etiqueta asociada.
     */
    public function label(): string
    {
        return match ($this) {
            static::PENDIENTE => 'Pendiente',
            static::VERIFICADO => 'Verificado',
            static::DENEGADO => 'Denegado',
        };
    }

    /**
     * Devuelve un array con todos los valores del enum.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
