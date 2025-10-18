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
            // Polymorphic fields - can be overridden when creating
            'addressable_type' => null,
            'addressable_id' => null,
        ];
    }
    
    /**
     * Set the addressable owner for polymorphic relation
     */
    public function for($owner)
    {
        return $this->state(fn (array $attributes) => [
            'addressable_type' => get_class($owner),
            'addressable_id' => $owner->id,
        ]);
    }
}
