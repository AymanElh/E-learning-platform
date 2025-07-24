<?php

use App\Models\Course;
use App\Models\Category;
use App\Models\Enrollment;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function() {
    // Create permissions for enrollment management
    $viewEnrollmentsPermission = Permission::firstOrCreate(['name' => 'view-enrollments']);
    $approveEnrollmentsPermission = Permission::firstOrCreate(['name' => 'approve-enrollments']);
    $deleteEnrollmentsPermission = Permission::firstOrCreate(['name' => 'delete-enrollments']);

    $role = Role::firstOrCreate(['name' => 'instructor']);
    $role->syncPermissions([$viewEnrollmentsPermission, $approveEnrollmentsPermission, $deleteEnrollmentsPermission]);

    $this->user = User::factory()->create();
    $this->instructor = User::factory()->create();
    $this->instructor->assignRole('instructor');

    $this->token = auth()->login($this->user);
    $this->instructorToken = auth()->login($this->instructor);

    $this->actingAs($this->user, 'api');
});

test('user can enroll in a course', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);

    $response = $this->postJson("/api/v1/courses/{$course->id}/enroll");

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Course Enrolled successfully'
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'user',
                'course',
                'status',
            ]
        ]);

    $this->assertDatabaseHas('enrollments', [
        'user_id' => $this->user->id,
        'course_id' => $course->id
    ]);
});

test('user cannot enroll twice in the same course', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);

    // First enrollment
    Enrollment::create([
        'user_id' => $this->user->id,
        'course_id' => $course->id,
        'status' => 'pending'
    ]);

    $response = $this->postJson("/api/v1/courses/{$course->id}/enroll");

    $response->assertStatus(409)
        ->assertJson([
            'success' => false,
            'message' => 'User is already enrolled this course'
        ]);
});

test('user can get their enrollments', function() {
    $category = Category::factory()->create();
    $course1 = Course::factory()->create(['category_id' => $category->id]);
    $course2 = Course::factory()->create(['category_id' => $category->id]);

    Enrollment::create([
        'user_id' => $this->user->id,
        'course_id' => $course1->id,
        'status' => 'accepted'
    ]);

    Enrollment::create([
        'user_id' => $this->user->id,
        'course_id' => $course2->id,
        'status' => 'pending'
    ]);

    $response = $this->getJson('/api/v1/enrollments/me');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Enrollments getted successfully'
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'course',
                    'user',
                    'status',
                ]
            ]
        ]);

    expect($response->json('data'))->toHaveCount(2);
});

test('user can cancel their enrollment', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);

    $enrollment = Enrollment::create([
        'user_id' => $this->user->id,
        'course_id' => $course->id,
        'status' => 'pending'
    ]);

    $response = $this->deleteJson("/api/v1/enrollments/{$enrollment->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Enroll cancelled successfully'
        ]);

    $this->assertDatabaseMissing('enrollments', [
        'id' => $enrollment->id
    ]);
});

test('instructor can get enrollments by course', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);

    // Create multiple enrollments for the course
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Enrollment::create([
        'user_id' => $user1->id,
        'course_id' => $course->id,
        'status' => 'pending'
    ]);

    Enrollment::create([
        'user_id' => $user2->id,
        'course_id' => $course->id,
        'status' => 'accepted'
    ]);

    $this->actingAs($this->instructor, 'api');
    $response = $this->getJson("/api/v1/courses/{$course->id}/enrollments");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Enrollments got successfully'
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'course',
                    'user',
                    'status',
                ]
            ]
        ]);

    expect($response->json('data'))->toHaveCount(2);
});

test('instructor can update enrollment status', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);

    $enrollment = Enrollment::create([
        'user_id' => $this->user->id,
        'course_id' => $course->id,
        'status' => 'pending'
    ]);

    $this->actingAs($this->instructor, 'api');
    $response = $this->patchJson("/api/v1/courses/{$course->id}/enrollments/{$enrollment->id}", [
        'status' => 'accepted'
    ]);
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Enrollment status updated successfully'
        ]);

    $this->assertDatabaseHas('enrollments', [
        'id' => $enrollment->id,
        'status' => 'accepted'
    ]);
});

test('enrollment status validation requires valid status', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);

    $enrollment = Enrollment::create([
        'user_id' => $this->user->id,
        'course_id' => $course->id,
        'status' => 'pending'
    ]);

    $this->actingAs($this->instructor, 'api');
    $response = $this->patchJson("/api/v1/courses/{$course->id}/enrollments/{$enrollment->id}", [
        'status' => 'invalid_status'
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('enrollment status validation accepts valid statuses', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);

    $validStatuses = ['pending', 'accepted', 'rejected'];

    foreach ($validStatuses as $status) {
        $enrollment = Enrollment::create([
            'user_id' => $this->user->id,
            'course_id' => $course->id,
            'status' => 'pending'
        ]);

        $this->actingAs($this->instructor, 'api');
        $response = $this->patchJson("/api/v1/courses/{$course->id}/enrollments/{$enrollment->id}", [
            'status' => $status
        ]);

        $response->assertStatus(200);

        // Clean up for next iteration
        $enrollment->delete();
    }
});

test('cannot enroll in non-existent course', function() {
    $response = $this->postJson('/api/v1/courses/999/enroll');
//    dd($response->json());
    $response->assertStatus(404);
});

test('cannot cancel non-existent enrollment', function() {
    $response = $this->deleteJson('/api/v1/enrollments/999');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Enrollment not found'
        ]);
});

test('unauthenticated user cannot enroll in course', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
    auth()->logout();
    $response = $this->postJson("/api/v1/courses/{$course->id}/enroll");
//    dd($response->json());
    $response->assertStatus(401);
});

test('unauthenticated user cannot access enrollments', function() {
    auth()->logout();
    $response = $this->getJson('/api/v1/enrollments/me');
    $response->assertStatus(401);
});

test('user cannot access another users enrollment data through my enrollments', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);

    $otherUser = User::factory()->create();

    // Create enrollment for other user
    Enrollment::create([
        'user_id' => $otherUser->id,
        'course_id' => $course->id,
        'status' => 'accepted'
    ]);

    // Create enrollment for authenticated user
    Enrollment::create([
        'user_id' => $this->user->id,
        'course_id' => $course->id,
        'status' => 'pending'
    ]);

    $response = $this->getJson('/api/v1/enrollments/me');
    $response->assertStatus(200);

    $enrollments = $response->json('data');

    // Should only see own enrollment
    expect($enrollments)->toHaveCount(1);
    expect($enrollments[0]['user']['id'])->toBe($this->user->id);
});

test('user without permission cannot view course enrollments', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
    $newUser = User::factory()->create();
    $this->actingAs($newUser, 'api');
    $response = $this->getJson("/api/v1/courses/{$course->id}/enrollments");
    $response->assertStatus(403);
});

test('user without permission cannot update enrollment status', function() {
    $category = Category::factory()->create();
    $course = Course::factory()->create(['category_id' => $category->id]);
    $newUser = User::factory()->create();
    $this->actingAs($newUser, 'api');

    $enrollment = Enrollment::create([
        'user_id' => $this->user->id,
        'course_id' => $course->id,
        'status' => 'pending'
    ]);

    $response = $this->patchJson("/api/v1/courses/{$course->id}/enrollments/{$enrollment->id}", [
        'status' => 'accepted'
    ]);

    $response->assertStatus(403);
});
