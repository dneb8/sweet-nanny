<?php

namespace Database\Seeders;

use App\Models\{Address, Booking, BookingService, Price};
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // 1. Crear 10 bookings con su factory (incluyendo recurrent)
        $bookings = Booking::factory()->count(10)->create();

        // 2. Recorrerlos y crear servicios según el valor de recurrent
        foreach ($bookings as $booking) {
            $servicesCount = $booking->recurrent
                ? $faker->numberBetween(2, 5)
                : 1;

            for ($i = 0; $i < $servicesCount; $i++) {
                $start = $faker->dateTimeBetween('+1 days', '+3 days');
                $end = (clone $start)->modify('+6 hours');

                $bookingService = BookingService::factory()->create([
                    'booking_id' => $booking->id,
                    'price_id' => Price::factory(),
                    'start_date' => $start,
                    'end_date' => $end,
                ]);

                $addresses = Address::factory()->count(rand(1, 2))->create();
                $bookingService->addresses()->attach($addresses->pluck('id')->toArray());
            }
        }
    }
}
