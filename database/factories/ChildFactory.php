<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\Tutor;
use App\Enums\Children\KinkshipEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChildFactory extends Factory
{
    protected $model = Child::class;

    public function definition(): array
    {
        return [
            'tutor_id' => Tutor::inRandomOrder()->first()?->id, // Usa un tutor existente
            'name' => $this->faker->firstName,
            'birthdate' => $this->faker->date('Y-m-d', '2015-12-31'),
            'kinkship' => $this->faker->randomElement(KinkshipEnum::values()),
        ];
    }
}