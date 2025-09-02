<?php

namespace Database\Factories;

use App\Models\{BookingService, Nanny};
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingServiceFactory extends Factory
{
    protected $model = BookingService::class;

    public function definition(): array
    {
        return [
            'nanny_id' => Nanny::inRandomOrder()->value('id'), // id de niñera random
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
            'payment_status' => $this->faker->randomElement(['unpaid', 'paid', 'refunded']),
            'extra_hours' => $this->faker->numberBetween(0, 3),
            'total_cost' => $this->faker->randomFloat(2, 100, 500),
        ];
    }
}
