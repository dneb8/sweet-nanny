<?php

namespace Database\Factories;

use App\Models\{Course, Nanny};
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(2),
            'organization' => $this->faker->company(),
            'date' => $this->faker->date(),
            'nanny_id' => Nanny::inRandomOrder()->first()?->id, // Usa una niñera existente
        ];
    }
}
