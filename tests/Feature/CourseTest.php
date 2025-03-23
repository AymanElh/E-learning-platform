<?php

use App\Models\Course;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function() {
    $createPermission = Permission::firstOrCreate(['name' => 'create courses']);
    $editPermission = Permission::firstOrCreate(['name' => 'edit courses']);
    $deletePermission = Permission::firstOrCreate(['name' => 'delete courses']);

    $role = Role::firstOrCreate(['name' => 'mentor']);
    $role->syncPermissions([$createPermission, $editPermission, $deletePermission]);

    $this->user = User::factory()->create();
    $this->user->assignRole('mentor');

    $this->token = auth()->login($this->user);

    $this->actingAs($this->user, 'api');
});

test('can get all courses', function() {
    // Create some categories and tags first
    $category = Category::factory()->create();
    $tags = Tag::factory(3)->create();

    // Create courses with relationships
    $courses = Course::factory(5)->create([
        'category_id' => $category->id
    ]);

    // Attach tags to courses
    $courses->each(function($course) use ($tags) {
        $course->tags()->attach($tags->random(2));
    });

    $response = $this->getJson('/api/v1/courses');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'duration',
                    'difficulty',
                    'status',
                    'category',
                    'tags'
                ]
            ]
        ]);
});

test('can fetch one course', function() {
    // Create necessary related data
    $category = Category::factory()->create();
    $tags = Tag::factory(2)->create();

    // Create a course
    $course = Course::factory()->create([
        'category_id' => $category->id
    ]);

    // Attach tags
    $course->tags()->attach($tags->pluck('id'));

    $response = $this->getJson("/api/v1/courses/{$course->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'title',
                'description',
                'duration',
                'difficulty',
                'status',
                'category',
                'tags'
            ]
        ]);
});

test('not found course', function() {
    $response = $this->getJson('/api/v1/courses/9999');
    $response->assertStatus(404);
});

test('can create a course', function() {
    // Create necessary related data first
    $category = Category::factory()->create();
    $tags = Tag::factory(2)->create();

    $data = [
        'title' => 'Laravel Mastery',
        'description' => 'Learn advanced Laravel concepts',
        'duration' => 120,
        'difficulty' => 'intermediate',
        'status' => 'open',
        'category_id' => $category->id,
        'tags' => $tags->pluck('id')->toArray()
    ];

    $response = $this->postJson('/api/v1/courses', $data);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => "Course created successfully"
        ]);

    $this->assertDatabaseHas('courses', [
        'title' => 'Laravel Mastery',
        'description' => 'Learn advanced Laravel concepts',
        'duration' => 120,
        'difficulty' => 'intermediate',
    ]);

    // Get the created course ID
    $courseId = $response->json('data.id');

    // Assert tags were attached properly
    foreach ($tags as $tag) {
        $this->assertDatabaseHas('course_tag', [
            'course_id' => $courseId,
            'tag_id' => $tag->id
        ]);
    }
});

test('can update course', function() {
    // Create necessary related data
    $category = Category::factory()->create();
    $newCategory = Category::factory()->create();
    $tags = Tag::factory(2)->create();
    $newTags = Tag::factory(2)->create();

    // Create a course
    $course = Course::factory()->create([
        'category_id' => $category->id
    ]);

    // Attach initial tags
    $course->tags()->attach($tags->pluck('id'));

    $update = [
        'title' => 'Updated Course Title',
        'description' => 'Updated description',
        'duration' => 150,
        'difficulty' => 'advanced',
        'status' => 'in_progress',
        'category_id' => $newCategory->id,
        'tags' => $newTags->pluck('id')->toArray()
    ];

    $response = $this->putJson("/api/v1/courses/{$course->id}", $update);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => "Course updated successfully"
        ]);

    // Assert course was updated
    $this->assertDatabaseHas('courses', [
        'id' => $course->id,
        'title' => 'Updated Course Title',
        'description' => 'Updated description',
        'duration' => 150,
        'difficulty' => 'advanced',
        'status' => 'in_progress',
        'category_id' => $newCategory->id
    ]);

    // Assert tags were updated correctly
    foreach ($newTags as $tag) {
        $this->assertDatabaseHas('course_tag', [
            'course_id' => $course->id,
            'tag_id' => $tag->id
        ]);
    }

    // Assert old tags were removed
    foreach ($tags as $tag) {
        $this->assertDatabaseMissing('course_tag', [
            'course_id' => $course->id,
            'tag_id' => $tag->id
        ]);
    }
});

test('can delete course', function() {
    // Create necessary related data
    $category = Category::factory()->create();
    $tags = Tag::factory(2)->create();

    // Create a course
    $course = Course::factory()->create([
        'category_id' => $category->id
    ]);

    // Attach tags
    $course->tags()->attach($tags->pluck('id'));

    $response = $this->deleteJson("/api/v1/courses/{$course->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => "Course deleted successfully"
        ]);

    // Assert course was deleted
    $this->assertDatabaseMissing('courses', ['id' => $course->id]);

    // Assert pivot table entries were deleted
    foreach ($tags as $tag) {
        $this->assertDatabaseMissing('course_tag', [
            'course_id' => $course->id,
            'tag_id' => $tag->id
        ]);
    }
});

test('validate required fields when creating course', function() {
    $response = $this->postJson('/api/v1/courses', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'duration', 'difficulty', 'category_id']);
});

test('validate course difficulty must be valid', function() {
    $category = Category::factory()->create();

    $response = $this->postJson('/api/v1/courses', [
        'title' => 'New Course',
        'duration' => 100,
        'difficulty' => 'invalid_level',  // Invalid value
        'category_id' => $category->id
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['difficulty']);
});

test('validate course status must be valid', function() {
    $category = Category::factory()->create();

    $response = $this->postJson('/api/v1/courses', [
        'title' => 'New Course',
        'duration' => 100,
        'difficulty' => 'beginner',
        'status' => 'invalid_status',  // Invalid value
        'category_id' => $category->id
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('validate category must exist', function() {
    $response = $this->postJson('/api/v1/courses', [
        'title' => 'New Course',
        'duration' => 100,
        'difficulty' => 'beginner',
        'category_id' => 9999  // Non-existent category
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['category_id']);
});

test('validate tags must exist', function() {
    $category = Category::factory()->create();

    $response = $this->postJson('/api/v1/courses', [
        'title' => 'New Course',
        'duration' => 100,
        'difficulty' => 'beginner',
        'category_id' => $category->id,
        'tags' => [9999, 8888]  // Non-existent tags
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['tags.0', 'tags.1']);
});
