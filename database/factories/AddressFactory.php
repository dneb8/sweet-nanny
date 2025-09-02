<?php

namespace Database\Factories;

use App\Enums\Address\TypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'postal_code' => $this->faker->postcode(),
            'street' => $this->faker->streetName(),
            'neighborhood' => $this->faker->citySuffix(),
            'type' => $type = $this->faker->randomElement(TypeEnum::values()),
            'other_type' => $type === 'other' ? $this->faker->word() : null,
            'internal_number' => $this->faker->optional()->buildingNumber(),
        ];
    }
}
