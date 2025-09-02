<?php

namespace Database\Factories;

use App\Models\{BookingService, Booking, Price};
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingServiceFactory extends Factory
{
    protected $model = BookingService::class;

    public function definition(): array
    {

        return [
            // 'nanny_id' => Nanny::factory(), 
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'extra_hours' => $this->faker->numberBetween(0, 3),
            'total_cost' => $this->faker->randomFloat(2, 100, 500),
        ];
    }
}


