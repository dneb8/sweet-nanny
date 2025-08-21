<?php

namespace Database\Factories;

use App\Models\Tutor;
use App\Models\Address;
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
        return [
            'description' => $this->faker->sentence(),
            'recurrent'   => $this->faker->boolean(),
            'tutor_id'    => Tutor::factory(),   
            'address_id'  => Address::factory(), 
        ];
    }
}
