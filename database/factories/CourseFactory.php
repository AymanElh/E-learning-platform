<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'duration' => $this->faker->numberBetween(1, 52) . ' weeks',
            'difficulty' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
            'status' => $this->faker->randomElement(['open', 'in_progress', 'completed']),
            'category_id' => \App\Models\Category::factory(),
        ];
    }
}
