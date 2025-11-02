<?php

namespace Database\Factories;

use App\Enums\Children\KinkshipEnum;
use App\Models\Child;
use App\Models\Tutor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ChildFactory extends Factory
{
    protected $model = Child::class;

    public function definition(): array
    {
        $today = Carbon::today();
        return [
            'tutor_id' => Tutor::inRandomOrder()->first()?->id, // Usa un tutor existente
            'name' => $this->faker->firstName,
            'birthdate' => $this->faker->dateTimeBetween(
                $today->copy()->subYears(14),
                $today->copy()->subYear()
            )->format('Y-m-d'),
            'kinkship' => $this->faker->randomElement(KinkshipEnum::values()),
        ];
    }
}
