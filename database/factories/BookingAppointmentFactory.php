<?php

namespace Database\Factories;

use App\Models\BookingAppointment;
use App\Models\Nanny;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BookingAppointmentFactory extends Factory
{
    protected $model = BookingAppointment::class;

    public function definition(): array
    {
        // 70% de probabilidad de tener niñera asignada
        $hasNanny = $this->faker->boolean(70);
        $nannyId  = $hasNanny ? Nanny::inRandomOrder()->value('id') : null;

        // Fechas: desde hace 3 días hasta dentro de 1 mes
        $startDate = $this->faker->dateTimeBetween('-3 days', '+1 month');
        $endDate   = (clone $startDate)->modify('+2 hours');

        // Determinar status
        $now    = Carbon::now();
        $status = 'pending';

        if ($nannyId) {
            if ($startDate > $now) {
                $status = 'confirmed';      // futuro con niñera
            } elseif ($startDate <= $now && $endDate >= $now) {
                $status = 'in_progress';    // en curso
            } else { // $endDate < $now
                $status = 'completed';      // ya finalizó
            }
        } else {
            // Sin niñera: pendiente si no ha pasado, cancelado si ya pasó
            if ($endDate < $now) {
                $status = 'cancelled';
            } else {
                $status = 'pending';
            }
        }

        return [
            'nanny_id'       => $nannyId,
            'start_date'     => $startDate,
            'end_date'       => $endDate,
            'status'         => $status,
            'payment_status' => $this->faker->randomElement(['unpaid', 'paid', 'refunded']),
            'extra_hours'    => $this->faker->numberBetween(0, 3),
            'total_cost'     => $this->faker->randomFloat(2, 100, 500),
        ];
    }
}
