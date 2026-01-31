<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Recipe::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'image' => null,
            'servings' => fake()->numberBetween(2, 8),
            'prep_time' => fake()->numberBetween(10, 60),
            'ingredients' => [
                fake()->word() . ' ' . fake()->numberBetween(100, 500) . 'g',
                fake()->word() . ' ' . fake()->numberBetween(1, 5) . ' Stk',
            ],
            'instructions' => [
                fake()->sentence(),
                fake()->sentence(),
                fake()->sentence(),
            ],
        ];
    }
}
