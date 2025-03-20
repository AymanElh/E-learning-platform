<?php

use App\Models\Course;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function() {
    Storage::fake('public');

    // Create permissions
    Permission::create(['name' => 'manage courses']);
    Permission::create(['name' => 'create courses']);
    Permission::create(['name' => 'edit courses']);

    // Create mentor role
    $role = Role::create(['name' => 'instructor']);
    $role->givePermissionTo(['create courses', 'edit courses', 'manage courses']);

    // Create user with instructor role
    $this->user = User::factory()->create();
    $this->user->assignRole('instructor');

    // Create course
    $this->course = Course::factory()->create();

    // Login
    $this->token = auth()->login($this->user);

    // Create a fake video file
    $this->videoFile = UploadedFile::fake()->create('sample.mp4', 1024, 'video/mp4');
});

test('instructor can add video to course', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->postJson("/api/v1/courses/{$this->course->id}/videos", [
        'title' => 'Introduction Video',
        'description' => 'An introductory video for the course',
        'video' => $this->videoFile,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);

    // Check database
    $this->assertDatabaseHas('videos', [
        'course_id' => $this->course->id,
        'title' => 'Introduction Video',
        'description' => 'An introductory video for the course',
    ]);

    // Check file was stored
    $video = Video::where('title', 'Introduction Video')->first();
    Storage::disk('public')->assertExists($video->file_path);
});

test('instructor can get course videos', function () {
    // Create some videos
    $video1 = Video::create([
        'course_id' => $this->course->id,
        'title' => 'Video 1',
        'description' => 'First video',
        'video_path' => 'path/to/video1.mp4',
    ]);

    $video2 = Video::create([
        'course_id' => $this->course->id,
        'title' => 'Video 2',
        'description' => 'Second video',
        'video_path' => 'path/to/video2.mp4',
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson("/api/v1/courses/{$this->course->id}/videos");
//    dd($response->json());
    $response->assertStatus(200)
        ->assertJson([
            'success' => true
        ])
        ->assertJsonCount(2, 'data');
});


test('instructor can update video details', function () {
    $video = Video::create([
        'course_id' => $this->course->id,
        'title' => 'Original Title',
        'description' => 'Original description',
        'video_path' => 'path/to/original.mp4'
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->putJson("/api/v1/videos/{$video->id}", [
        'title' => 'Updated Title',
        'description' => 'Updated description',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Video updated successfully'
        ]);

    $this->assertDatabaseHas('videos', [
        'id' => $video->id,
        'title' => 'Updated Title',
        'description' => 'Updated description',
    ]);
});

test('instructor can update video with new file', function () {
    $video = Video::create([
        'course_id' => $this->course->id,
        'title' => 'Original Video',
        'description' => 'Original video file',
        'video_path' => 'path/to/original.mp4'
    ]);

    $newVideo = UploadedFile::fake()->create('new.mp4', 1024, 'video/mp4');
//    dd($newVideo);
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->putJson("/api/v1/videos/{$video->id}", [
        'title' => 'Updated Video',
        'video_path' => $newVideo,
    ]);
//    dd($response->json());
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Video updated successfully'
        ]);

    // Check database was updated
    $this->assertDatabaseHas('videos', [
        'id' => $video->id,
        'title' => 'Updated Video',
    ]);

    $updatedVideo = Video::find($video->id);
    $this->assertNotEquals('path/to/original.mp4', $updatedVideo->file_path);

    Storage::disk('public')->assertExists($updatedVideo->file_path);
});

test('instructor can delete video', function () {
    $video = Video::create([
        'course_id' => $this->course->id,
        'title' => 'Temporary Video',
        'description' => 'Will be deleted',
        'video_path' => 'path/to/delete.mp4'
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->deleteJson("/api/v1/videos/{$video->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Video deleted successfully'
        ]);

    $this->assertDatabaseMissing('videos', [
        'id' => $video->id,
    ]);
});

