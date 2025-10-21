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
        $tutor = Tutor::query()->with('addresses')->inRandomOrder()->first()
            ?? Tutor::factory()->hasAddresses(2)->create();

        $address = $tutor->addresses()->inRandomOrder()->first();

        return [
            'tutor_id'   => $tutor->id,
            'address_id' => $address?->id,
            'description' => $this->faker->sentence(),
            'recurrent'   => $this->faker->boolean(),
            'qualities'   => [],
            'careers'     => [],
            'courses'     => [],
        ];
    }
}
