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

        $now = Carbon::now();

        // Regla principal: al crear, por defecto es DRAFT.
        // Si hay niñera asignada, puede estar en pending/confirmed/in_progress/completed según fechas.
        // Si NO hay niñera, permanece en draft (o cancelled si ya pasó).
        $status = 'draft';

        if ($nannyId) {
            if ($startDate > $now) {
                // Futuro con niñera asignada: está pendiente de confirmación de la niñera
                $status = 'pending';
            } elseif ($startDate <= $now && $endDate >= $now) {
                // En ventana de servicio: solo si asumimos que ya fue confirmada
                $status = 'in_progress';
            } else { // $endDate < $now
                // Terminó: solo si asumimos que estuvo confirmada antes
                $status = 'completed';
            }
        } else {
            // Sin niñera: si ya pasó la ventana, lo marcamos como cancelado por no haberse concretado
            if ($endDate < $now) {
                $status = 'cancelled';
            } else {
                // A futuro y sin niñera -> se queda en draft
                $status = 'draft';
            }
        }

        return [
            'nanny_id'       => $nannyId,
            'start_date'     => $startDate,
            'end_date'       => $endDate,
            'status'         => $status, // <- ahora respeta el flujo
            'payment_status' => $this->faker->randomElement(['unpaid', 'paid', 'refunded']),
            'extra_hours'    => $this->faker->numberBetween(0, 3),
            'total_cost'     => $this->faker->randomFloat(2, 100, 500),
        ];
    }
}
