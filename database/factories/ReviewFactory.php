<?php

namespace Database\Factories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        $reviewableType = collect([
            \App\Models\Tutor::class,
            \App\Models\Nanny::class,
        ])->random();
        $reviewableId = $reviewableType::inRandomOrder()->first()?->id ?? $reviewableType::factory()->create()->id;

        return [
            'reviewable_type' => $reviewableType,
            'reviewable_id' => $reviewableId,
            'rating' => $this->faker->numberBetween(1, 5),
            'comments' => $this->faker->sentence(),
        ];
    }
}
