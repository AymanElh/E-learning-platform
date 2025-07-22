<?php

use App\Models\Course;
use App\Models\Section;
use App\Models\Category;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function() {
    $createPermission = Permission::firstOrCreate(['name' => 'create-courses']);
    $editPermission = Permission::firstOrCreate(['name' => 'edit-courses']);
    $deletePermission = Permission::firstOrCreate(['name' => 'delete-courses']);

    $role = Role::firstOrCreate(['name' => 'mentor']);
    $role->syncPermissions([$createPermission, $editPermission, $deletePermission]);

    $this->user = User::factory()->create();
    $this->user->assignRole('mentor');

    $this->token = auth()->login($this->user);

    $this->actingAs($this->user, 'api');
});

test('can get all sections for a course', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);

    Section::factory(3)->create([
        'course_id' => $course->id,
        'order_index' => 1
    ]);

    $response = $this->getJson("/api/v1/courses/{$course->id}/sections");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'order_index',
                    'is_published'
                ]
            ]
        ]);
});

test('can create a section for a course', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
//    dd($course);
    $data = [
        'title' => 'Introduction to Laravel',
        'description' => 'Learn the basics of Laravel framework',
        'order_index' => 1,
        'is_published' => true
    ];

    $response = $this->postJson("/api/v1/courses/{$course->id}/sections", $data);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Section added to the course successfully'
        ]);

    $this->assertDatabaseHas('sections', [
        'course_id' => $course->id,
        'title' => 'Introduction to Laravel',
        'description' => 'Learn the basics of Laravel framework',
        'order_index' => 1,
        'is_published' => true
    ]);
});

test('can show a specific section', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
    $section = Section::factory()->create([
        'course_id' => $course->id,
        'order_index' => 1
    ]);

    $response = $this->getJson("/api/v1/courses/{$course->id}/sections/{$section->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'title',
                'description',
                'order_index',
                'is_published'
            ]
        ]);
});

test('can update a section', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
    $section = Section::factory()->create([
        'course_id' => $course->id,
        'order_index' => 1
    ]);

    $updateData = [
        'title' => 'Updated Section Title',
        'description' => 'Updated section description',
        'order_index' => 2,
        'is_published' => false
    ];

    $response = $this->putJson("/api/v1/courses/{$course->id}/sections/{$section->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Section updated successfully'
        ]);

    $this->assertDatabaseHas('sections', [
        'id' => $section->id,
        'title' => 'Updated Section Title',
        'description' => 'Updated section description',
        'order_index' => 2,
        'is_published' => false
    ]);
});

test('can delete a section without lessons', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
    $section = Section::factory()->create([
        'course_id' => $course->id,
        'order_index' => 1
    ]);
//    dump($section);
    $response = $this->deleteJson("/api/v1/courses/{$course->id}/sections/{$section->id}");
//    dd($response->json());
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Section deleted successfully'
        ]);

    $this->assertDatabaseMissing('sections', ['id' => $section->id]);
});

test('cannot delete section with lessons', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
    $section = Section::factory()->create([
        'course_id' => $course->id,
        'order_index' => 1
    ]);

    // Create a lesson in this section
    $section->lessons()->create([
        'title' => 'Test Lesson',
        'lesson_type' => 'video',
        'order_index' => 1,
        'is_published' => true
    ]);

    $response = $this->deleteJson("/api/v1/courses/{$course->id}/sections/{$section->id}");

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Cannot delete section with lessons. Please delete lessons first.'
        ]);

    $this->assertDatabaseHas('sections', ['id' => $section->id]);
});

test('returns 404 for non-existent section', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);

    $response = $this->getJson("/api/v1/courses/{$course->id}/sections/9999");
//    dd($response->json());
    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Section not found on this course'
        ]);
});

test('validates required fields when creating section', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);

    $response = $this->postJson("/api/v1/courses/{$course->id}/sections", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

test('validates section title uniqueness', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);

    Section::factory()->create([
        'course_id' => $course->id,
        'title' => 'Existing Section',
        'order_index' => 1
    ]);

    $response = $this->postJson("/api/v1/courses/{$course->id}/sections", [
        'title' => 'Existing Section',
        'order_index' => 2
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

test('validates order_index must be positive integer', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);

    $response = $this->postJson("/api/v1/courses/{$course->id}/sections", [
        'title' => 'Test Section',
        'order_index' => 0
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['order_index']);
});
