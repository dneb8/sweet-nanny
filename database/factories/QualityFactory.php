<?php

namespace Database\Factories;

use App\Models\Quality;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quality>
 */
class QualityFactory extends Factory
{
    protected $model = Quality::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->unique()->word()),
        ];
    }
}
