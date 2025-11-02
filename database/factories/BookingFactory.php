<?php

namespace Database\Factories;

use App\Models\Tutor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tutor = Tutor::query()->inRandomOrder()->first()
            ?? Tutor::factory()->create();

        return [
            'tutor_id' => $tutor->id,
            'description' => $this->faker->sentence(),
            'recurrent' => $this->faker->boolean(),
            'qualities' => [],
            'careers' => [],
            'courses' => [],
        ];
    }
}
