<?php

namespace App\Enums\Booking;

enum StatusEnum: string
{
    case DRAFT      = 'draft';
    case PENDING    = 'pending';      // Tiene niñera asignada, pero aún no inicia
    case CONFIRMED  = 'confirmed';    // Confirmado (por el sistema o admin)
    case CANCELLED   = 'cancelled';     // Cancelado
    case IN_PROGRESS= 'in_progress';  // Servicio actualmente en curso
    case COMPLETED  = 'completed';    // Servicio finalizado

    public function label(): string
    {
        return match ($this) {
            self::DRAFT       => 'Borrador',
            self::PENDING     => 'Pendiente',
            self::CONFIRMED   => 'Confirmado',
            self::CANCELLED    => 'Cancelado',
            self::IN_PROGRESS => 'En curso',
            self::COMPLETED   => 'Finalizado',
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
