<?php

namespace Database\Factories;

use App\Models\Career;
use Illuminate\Database\Eloquent\Factories\Factory;

class CareerFactory extends Factory
{
    protected $model = Career::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->jobTitle(), // Profesión o carrera aleatoria
        ];
    }
}
