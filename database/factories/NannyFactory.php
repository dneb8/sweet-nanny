<?php

namespace Database\Factories;

use App\Models\{Nanny, User};
use Illuminate\Database\Eloquent\Factories\Factory;

class NannyFactory extends Factory
{
    protected $model = Nanny::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Aún no existen users
            'bio' => $this->faker->paragraph(),
            'availability' => $this->faker->boolean(),
            'start_date' => $this->faker->date(),
            'address_id' => null, // Aún no existen addresses

        ];
    }
}
