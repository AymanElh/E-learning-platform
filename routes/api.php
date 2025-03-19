<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\EnrollmentController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\StatisticsController;
use App\Http\Controllers\Api\V1\VideoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\RoleController;


Route::prefix('v1')->group(function() {
    Route::apiResource('/tags', TagController::class);
    Route::apiResource('/categories', CategoryController::class);
    Route::apiResource('/courses', CourseController::class);

    Route::post('/courses/{course}/tags', [CourseController::class, 'attachTags']);
    Route::put('/courses/{course}/tags', [CourseController::class, 'syncTags']);
    Route::delete('/courses/{course}/tags', [CourseController::class, 'detachTags']);

    // Authentication
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile-picture', [AuthController::class, 'uploadProfilePicture']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Enrollments
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'enroll']);
    Route::get('/courses/{course}/enrollments', [EnrollmentController::class, 'getEnrollmentsByCourse']);
    Route::put('/enrollments/{enrollment}', [EnrollmentController::class, 'updateStatus']);
    Route::get('/enrollments/me', [EnrollmentController::class, 'myEnrollments']);
    Route::delete('/enrollments/{enrollment}', [EnrollmentController::class, 'destroy']);

    // roles & permissions
    Route::apiResource('/roles', RoleController::class);
    Route::post('/roles/{role}/permissions', [RoleController::class, 'assignPermissions']);
    Route::delete('/roles/{role}/permissions', [RoleController::class, 'removePermissions']);

    Route::apiResource('/permissions', PermissionController::class);

    // Course videos
    Route::get('/courses/{course}/videos', [VideoController::class, 'index']);
    Route::post('/courses/{course}/videos', [VideoController::class, 'store']);
    Route::put('/videos/{video}', [VideoController::class, 'update']);
    Route::delete('/videos/{video}', [VideoController::class, 'destroy']);

    // stats
    Route::prefix('stats')->group(function() {
        Route::get('/categories', [StatisticsController::class, 'getCategoryStats']);
        Route::get('/tags', [StatisticsController::class, 'getTagStats']);
        Route::get('/courses', [StatisticsController::class, 'getCourseStats']);
    });

});

