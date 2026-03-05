<?php

namespace Database\Factories;

use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipment>
 */
class EquipmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Equipment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'image' => null,
            'link' => fake()->url(),
            'category' => fake()->randomElement(['Messer', 'Töpfe', 'Elektro', 'Backen']),
            'price' => fake()->optional(0.7)->randomElement(['19,99 €', '29,99 €', '49,90 €', null]),
            'original_price' => null,
            'discount_percentage' => null,
            'last_price_checked_at' => null,
        ];
    }
}
