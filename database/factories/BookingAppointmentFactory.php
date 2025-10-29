<?php

namespace Database\Factories;

use App\Models\{BookingAppointment, Nanny};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BookingAppointmentFactory extends Factory
{
    protected $model = BookingAppointment::class;

    public function definition(): array
    {
        $hasNanny = $this->faker->boolean(70); // 70% de probabilidad de tener niñera asignada
        $nannyId = $hasNanny ? Nanny::inRandomOrder()->value('id') : null;

        // Generamos fechas
        $startDate = $this->faker->dateTimeBetween('-3 days', '+3 days');
        $endDate = (clone $startDate)->modify('+2 hours');

        // Determinar status automáticamente
        $now = Carbon::now();
        $status = 'pending';

        if ($nannyId) {
            if ($startDate > $now) {
                $status = 'confirmed'; // servicio futuro con niñera asignada
            } elseif ($startDate <= $now && $endDate >= $now) {
                $status = 'in_progress'; // servicio en curso
            } elseif ($endDate < $now) {
                $status = 'completed'; // servicio ya finalizó
            }
        }

        return [
            'nanny_id' => $nannyId,
            'status' => $status,
            'payment_status' => $this->faker->randomElement(['unpaid', 'paid', 'refunded']),
            'extra_hours' => $this->faker->numberBetween(0, 3),
            'total_cost' => $this->faker->randomFloat(2, 100, 500),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}
