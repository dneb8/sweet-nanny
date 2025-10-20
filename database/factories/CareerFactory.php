<?php

namespace Database\Factories;

use App\Models\Career;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\Career\NameCareerEnum; 

class CareerFactory extends Factory
{
    protected $model = Career::class;

    public function definition(): array
    {
        $allCareerEnums = NameCareerEnum::cases();
        $randomCareer = $this->faker->randomElement($allCareerEnums);
        return [
            'name' => $randomCareer->value,
        ];
    }
}
