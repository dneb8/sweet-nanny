<?php

namespace Database\Factories;

use App\Models\Price;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceFactory extends Factory
{
    protected $model = Price::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement([
                'Hogar',
                'Evento',
                'Alberca',
            ]),

            // Precio base
            'cost' => $this->faker->randomFloat(2, 100, 300),

            // Precios adicionales
            'extra_hours' => $this->faker->randomFloat(2, 0.1, 0.3),
            'night_shift' => $this->faker->randomFloat(2, 0.1, 0.3),
            'special_care' => $this->faker->randomFloat(2, 0.1, 0.3),
        ];

    }
}
