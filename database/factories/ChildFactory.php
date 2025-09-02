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
            'tutor_id' => Tutor::factory(),
            'name' => $this->faker->firstName,
            'birthdate' => $this->faker->date('Y-m-d', '2015-12-31'),
            'kinkship' => $this->faker->randomElement(['hijo', 'sobrino', 'primo', 'hermano', 'otro']),
            #'kinkship' => $this->faker->randomElement(KinkshipEnum::cases())->value,
        ];
    }
}