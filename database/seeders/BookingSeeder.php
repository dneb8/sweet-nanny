<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Booking, BookingAppointment, Nanny, Tutor};
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        Booking::factory(10)
            ->create()
            ->each(function ($booking) {
                $now = Carbon::now();

                // 70% de las reservas tendrán niñera asignada
                $hasNanny = fake()->boolean(70);
                $nannyId = $hasNanny ? Nanny::inRandomOrder()->value('id') : null;

                // Generamos 1 o 2 citas por reserva
                BookingAppointment::factory(fake()->numberBetween(1, 2))
                    ->make([
                        'booking_id' => $booking->id,
                        'nanny_id'   => $nannyId,
                    ])
                    ->each(function ($appointment) use ($nannyId, $now) {
                        $start = Carbon::instance(fake()->dateTimeBetween('-3 days', '+3 days'));
                        $end   = (clone $start)->addHours(fake()->numberBetween(1, 8));

                        // Determinar status según reglas
                        if ($nannyId) {
                            if ($end->isPast()) {
                                $status = 'completed'; // ya terminó
                            } elseif ($start->isFuture()) {
                                $status = 'confirmed'; // futura, ya confirmada
                            } else {
                                $status = 'in_progress'; // está ocurriendo ahora
                            }
                        } else {
                            $status = 'pending'; // sin niñera aún
                        }

                        $appointment->fill([
                            'status'        => $status,
                            'start_date'    => $start,
                            'end_date'      => $end,
                        ])->save();
                    });
            });
    }
}
