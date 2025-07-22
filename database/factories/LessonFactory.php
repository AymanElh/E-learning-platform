<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'section_id' => Section::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'lesson_type' => $this->faker->randomElement(['video', 'article', 'quiz', 'assignment']),
            'order_index' => $this->faker->numberBetween(1, 20),
            'duration_minutes' => $this->faker->numberBetween(5, 120),
            'is_free_preview' => $this->faker->boolean(30), // 30% chance of being free preview
            'is_published' => $this->faker->boolean(80), // 80% chance of being published
        ];
    }

    /**
     * Indicate that the lesson is a video lesson.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'lesson_type' => 'video',
            'duration_minutes' => $this->faker->numberBetween(10, 60),
        ]);
    }

    /**
     * Indicate that the lesson is an article lesson.
     */
    public function article(): static
    {
        return $this->state(fn (array $attributes) => [
            'lesson_type' => 'article',
            'duration_minutes' => $this->faker->numberBetween(5, 30),
        ]);
    }

    /**
     * Indicate that the lesson is a quiz.
     */
    public function quiz(): static
    {
        return $this->state(fn (array $attributes) => [
            'lesson_type' => 'quiz',
            'duration_minutes' => $this->faker->numberBetween(5, 15),
        ]);
    }

    /**
     * Indicate that the lesson is an assignment.
     */
    public function assignment(): static
    {
        return $this->state(fn (array $attributes) => [
            'lesson_type' => 'assignment',
            'duration_minutes' => $this->faker->numberBetween(30, 120),
        ]);
    }

    /**
     * Indicate that the lesson is a free preview.
     */
    public function freePreview(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_free_preview' => true,
        ]);
    }

    /**
     * Indicate that the lesson is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }

    /**
     * Indicate that the lesson is unpublished.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }
}
