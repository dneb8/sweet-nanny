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
            'tutor_id' => Tutor::inRandomOrder()->first()?->id,
            'qualities' => [],
            'degree' => null,
            'courses' => [],
        ];
    }
    
    /**
     * Configure the factory to create an address after the booking is created
     */
    public function configure()
    {
        return $this->afterCreating(function (\App\Models\Booking $booking) {
            // Create address via polymorphic relation
            $booking->address()->create([
                'postal_code' => fake()->postcode(),
                'street' => fake()->streetName(),
                'neighborhood' => fake()->citySuffix(),
                'type' => fake()->randomElement(\App\Enums\Address\TypeEnum::values()),
                'internal_number' => fake()->optional()->buildingNumber(),
            ]);
        });
    }
}
