<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\{Address, Booking, BookingAppointment, Nanny, Child};

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // 1) Crear 10 bookings (address is created automatically via factory's afterCreating)
        $bookings = Booking::factory()->count(10)->create();

        // 2) Por booking: citas y niños
        foreach ($bookings as $booking) {
            // --- Citas ---
            $nannyId = Nanny::inRandomOrder()->value('id');
            $servicesCount = $booking->recurrent ? $faker->numberBetween(5, 10) : 1;

            for ($i = 0; $i < $servicesCount; $i++) {
                $start = $faker->dateTimeBetween('+1 days', '+3 days');
                $end   = (clone $start)->modify('+6 hours');

                BookingAppointment::factory()->create([
                    'booking_id' => $booking->id,
                    'nanny_id'   => $nannyId,
                    'start_date' => $start,
                    'end_date'   => $end,
                ]);
            }

            // --- Niños: crea con factory y anexa al booking ---
            if ($booking->tutor_id) {
                $childrenCount = $faker->numberBetween(1, 3);

                // Crea N niños para el tutor del booking
                $createdChildIds = Child::factory()
                    ->count($childrenCount)
                    ->for($booking->tutor, 'tutor')   // setea tutor_id
                    ->create()
                    ->pluck('id')
                    ->all();

                // Anexa a la pivote (sin duplicar si re-seedeas)
                $booking->children()->syncWithoutDetaching($createdChildIds);
            }
        }
    }
}
