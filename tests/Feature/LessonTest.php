<?php

use App\Models\Course;
use App\Models\Section;
use App\Models\Lesson;
use App\Models\Category;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function() {
    $createPermission = Permission::firstOrCreate(['name' => 'create-courses']);
    $editPermission = Permission::firstOrCreate(['name' => 'edit-courses']);
    $deletePermission = Permission::firstOrCreate(['name' => 'delete-courses']);

    $role = Role::firstOrCreate(['name' => 'instructor']);
    $role->syncPermissions([$createPermission, $editPermission, $deletePermission]);

    $this->user = User::factory()->create();
    $this->user->assignRole('instructor');

    $this->token = auth()->login($this->user);

    $this->actingAs($this->user, 'api');
});

test('can get all lessons for a section', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
    $section = Section::factory()->create([
        'course_id' => $course->id,
        'order_index' => 1
    ]);

    Lesson::factory(3)->create([
        'section_id' => $section->id,
        'lesson_type' => 'video',
        'order_index' => 1
    ]);

    $response = $this->getJson("/api/v1/courses/{$course->id}/sections/{$section->id}/lessons");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'lesson_type',
                    'order_index',
                    'duration_minutes',
                    'is_free_preview',
                    'is_published'
                ]
            ]
        ]);
});

test('can create a lesson for a section', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
    $section = Section::factory()->create([
        'course_id' => $course->id,
        'order_index' => 1
    ]);

    $data = [
        'title' => 'Introduction to Variables',
        'description' => 'Learn about PHP variables',
        'lesson_type' => 'video',
        'order_index' => 1,
        'duration_minutes' => 15,
        'is_free_preview' => true,
        'is_published' => true
    ];

    $response = $this->postJson("/api/v1/courses/{$course->id}/sections/{$section->id}/lessons", $data);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Lesson created successfully'
        ]);

    $this->assertDatabaseHas('lessons', [
        'section_id' => $section->id,
        'title' => 'Introduction to Variables',
        'description' => 'Learn about PHP variables',
        'lesson_type' => 'video',
        'order_index' => 1,
        'duration_minutes' => 15,
        'is_free_preview' => true,
        'is_published' => true
    ]);
});

test('can show a specific lesson', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
    $section = Section::factory()->create([
        'course_id' => $course->id,
        'order_index' => 1
    ]);
    $lesson = Lesson::factory()->create([
        'section_id' => $section->id,
        'lesson_type' => 'video',
        'order_index' => 1
    ]);

    $response = $this->getJson("/api/v1/courses/{$course->id}/sections/{$section->id}/lessons/{$lesson->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'title',
                'description',
                'lesson_type',
                'order_index',
                'duration_minutes',
                'is_free_preview',
                'is_published'
            ]
        ]);
});

test('can update a lesson', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
    $section = Section::factory()->create([
        'course_id' => $course->id,
        'order_index' => 1
    ]);
    $lesson = Lesson::factory()->create([
        'section_id' => $section->id,
        'lesson_type' => 'video',
        'order_index' => 1
    ]);

    $updateData = [
        'title' => 'Updated Lesson Title',
        'description' => 'Updated lesson description',
        'lesson_type' => 'article',
        'order_index' => 2,
        'duration_minutes' => 30,
        'is_free_preview' => false,
        'is_published' => false
    ];

    $response = $this->putJson("/api/v1/courses/{$course->id}/sections/{$section->id}/lessons/{$lesson->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Lesson updated successfully'
        ]);

    $this->assertDatabaseHas('lessons', [
        'id' => $lesson->id,
        'title' => 'Updated Lesson Title',
        'description' => 'Updated lesson description',
        'lesson_type' => 'article',
        'order_index' => 2,
        'duration_minutes' => 30,
        'is_free_preview' => false,
        'is_published' => false
    ]);
});

test('can delete a lesson', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
    $section = Section::factory()->create([
        'course_id' => $course->id,
        'order_index' => 1
    ]);
    $lesson = Lesson::factory()->create([
        'section_id' => $section->id,
        'lesson_type' => 'video',
        'order_index' => 1
    ]);

    $response = $this->deleteJson("/api/v1/courses/{$course->id}/sections/{$section->id}/lessons/{$lesson->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Lesson deleted successfully'
        ]);

    $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);
});

test('returns 404 for non-existent lesson', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
    $section = Section::factory()->create([
        'course_id' => $course->id,
        'order_index' => 1
    ]);

    $response = $this->getJson("/api/v1/courses/{$course->id}/sections/{$section->id}/lessons/9999");

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Lesson or section not found for this course'
        ]);
});

test('returns 404 for non-existent section when accessing lessons', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);

    $response = $this->getJson("/api/v1/courses/{$course->id}/sections/9999/lessons");

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Section doesn\'t exist for this course'
        ]);
});

test('validates required fields when creating lesson', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
    $section = Section::factory()->create([
        'course_id' => $course->id,
        'order_index' => 1
    ]);

    $response = $this->postJson("/api/v1/courses/{$course->id}/sections/{$section->id}/lessons", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'lesson_type']);
});

test('validates lesson_type must be valid', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
    $section = Section::factory()->create([
        'course_id' => $course->id,
        'order_index' => 1
    ]);

    $response = $this->postJson("/api/v1/courses/{$course->id}/sections/{$section->id}/lessons", [
        'title' => 'Test Lesson',
        'lesson_type' => 'invalid_type'
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['lesson_type']);
});

test('validates order_index must be positive integer', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
    $section = Section::factory()->create([
        'course_id' => $course->id,
        'order_index' => 1
    ]);

    $response = $this->postJson("/api/v1/courses/{$course->id}/sections/{$section->id}/lessons", [
        'title' => 'Test Lesson',
        'lesson_type' => 'video',
        'order_index' => 0
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['order_index']);
});

test('validates duration_minutes must be positive integer', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
    $section = Section::factory()->create([
        'course_id' => $course->id,
        'order_index' => 1
    ]);

    $response = $this->postJson("/api/v1/courses/{$course->id}/sections/{$section->id}/lessons", [
        'title' => 'Test Lesson',
        'lesson_type' => 'video',
        'duration_minutes' => 0
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['duration_minutes']);
});
