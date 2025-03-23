<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


test('user can enroll in a course', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create();

    $token = auth()->login($user);
//    dump($token);
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson("/api/v1/courses/{$course->id}/enroll");

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Course enrolled successfully',
        ]);

    $this->assertDatabaseHas('enrollments', [
        'user_id' => $user->id,
        'course_id' => $course->id,
        'status' => 'pending'
    ]);
});

test('can get course enrollments', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $course = Course::factory()->create();

    // Create enrollments
    Enrollment::create([
        'user_id' => $user1->id,
        'course_id' => $course->id,
        'status' => 'accepted'
    ]);

    Enrollment::create([
        'user_id' => $user2->id,
        'course_id' => $course->id,
        'status' => 'pending'
    ]);

    $token = auth()->login($user1);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson("/api/v1/courses/{$course->id}/enrollments");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
});

test('user can get their enrollments', function () {
    $user = User::factory()->create();
    $course1 = Course::factory()->create();
    $course2 = Course::factory()->create();

    // Create enrollments
    Enrollment::create([
        'user_id' => $user->id,
        'course_id' => $course1->id,
        'status' => 'accepted'
    ]);

    Enrollment::create([
        'user_id' => $user->id,
        'course_id' => $course2->id,
        'status' => 'pending'
    ]);

    $token = auth()->login($user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson("/api/v1/enrollments/me");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
});
