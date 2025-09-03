<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use App\Models\Tutor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tutor>
 */
class TutorFactory extends Factory
{
    protected $model = Tutor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), 
            'address_id' => Address::factory(),
            'emergency_contact' => $this->faker->optional()->name(),
            'emergency_number' => $this->faker->optional()->phoneNumber(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Tutor $tutor) {
            $tutor->user->assignRole('tutor'); 
        });
    }
}
