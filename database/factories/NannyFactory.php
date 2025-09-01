<?php

namespace Database\Factories;

use App\Models\{Nanny, User, Address};
use Illuminate\Database\Eloquent\Factories\Factory;

class NannyFactory extends Factory
{
    protected $model = Nanny::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(), 
            'bio' => $this->faker->paragraph(),
            'availability' => $this->faker->boolean(),
            'start_date' => $this->faker->date(),
            'address_id' => Address::factory(), 
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Nanny $nanny) {
            $nanny->user->assignRole('nanny'); 
        });
    }
}
