<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\RecipeReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecipeReview>
 */
class RecipeReviewFactory extends Factory
{
    protected $model = RecipeReview::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipe_id' => Recipe::factory(),
            'user_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'body' => fake()->optional(0.7)->paragraph(),
        ];
    }
}
