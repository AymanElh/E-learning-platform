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
        $title = $this->faker->sentence(3);

        return [
            'title' => $title,
            'description' => $this->faker->paragraph,
            'slug' => \Illuminate\Support\Str::slug($title),
            'duration' => $this->faker->numberBetween(1, 100),
            'difficulty' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
            'status' => $this->faker->randomElement(['open', 'closed']),
            'price' => $this->faker->randomFloat(2, 0, 999.99),
            'is_free' => $this->faker->boolean(20), // 20% chance of being free
            'is_published' => $this->faker->boolean(80), // 80% chance of being published
            'is_featured' => $this->faker->boolean(30), // 30% chance of being featured
            'thumbnail_url' => $this->faker->imageUrl(640, 480, 'education'),
            'instructor_id' => \App\Models\User::factory(),
            'category_id' => \App\Models\Category::factory(),
            'subcategory_id' => null,
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the course is free.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => 0,
            'is_free' => true,
        ]);
    }

    /**
     * Indicate that the course is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the course is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the course is unpublished.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }
}
