<?php

use App\Http\Controllers\Api\V1\Admin\PermissionController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Admin\StatisticsController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Content\CategoryController;
use App\Http\Controllers\Api\V1\Content\TagController;
use App\Http\Controllers\Api\V1\Course\CourseController;
use App\Http\Controllers\Api\V1\Course\EnrollmentController;
use App\Http\Controllers\Api\V1\Course\LessonController;
use App\Http\Controllers\Api\V1\Course\SectionController;
use App\Http\Controllers\Api\V1\VideoController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function() {
    // public routes
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Routes that require authentication
    Route::middleware('auth:api')->group(function() {

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
        Route::put('/profile', [AuthController::class, 'updateProfile'])->name('update-profile');
        Route::post('/profile-picture', [AuthController::class, 'uploadProfilePicture']);

        // Tag
        Route::get('/tags', [TagController::class, 'index']);
        Route::get('/tags/{tag}', [TagController::class, 'show']);
        Route::middleware('permission:create-tags')->post('/tags', [TagController::class, 'store']);
        Route::middleware('permission:edit-tags')->put('/tags/{tag}', [TagController::class, 'update']);
        Route::middleware('permission:delete-tags')->delete('/tags/{tag}', [TagController::class, 'destroy']);

        // Category
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/categories/{category}', [CategoryController::class, 'show']);
        Route::middleware('permission:create-categories')->post('/categories', [CategoryController::class, 'store']);
        Route::middleware('permission:edit-categories')->put('/categories/{category}', [CategoryController::class, 'update']);
        Route::middleware('permission:delete-categories')->delete('/categories/{category}', [CategoryController::class, 'destroy']);
        Route::get('/categories/{category}/children', [CategoryController::class, 'children']);

        // Course routes with permission checks
        Route::get('/courses', [CourseController::class, 'index']);
        Route::get('/courses/{course}', [CourseController::class, 'show']);
        Route::middleware('permission:create-courses')->post('/courses', [CourseController::class, 'store']);
        Route::middleware('permission:edit-courses')->put('/courses/{course}', [CourseController::class, 'update']);
        Route::middleware('permission:delete-courses')->delete('/courses/{course}', [CourseController::class, 'destroy']);

        // Course Sections
        Route::prefix('/courses/{course}')->group(function() {
            Route::get('/sections', [SectionController::class, 'index']);
            Route::post('/sections', [SectionController::class, 'store'])->middleware('permission:create-courses');
            Route::put('/sections/{section}', [SectionController::class, 'update'])->middleware('permission:edit-courses');
            Route::delete('/sections/{section}', [SectionController::class, 'destroy'])->middleware('permission:delete-courses');

            // Lessons routes
            Route::prefix('/sections/{sectionId}')->group(function() {
                Route::apiResource('/lessons', LessonController::class);
//                Route::get('/lessons', [LessonController::class, 'index']);
//                Route::post('/lessons', [LessonController::class, 'store']);
//                Route::put('/lessons/{lesson}', [LessonController::class, 'update']);
//                Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy']);
            });
        });

        // Course tag management with permission checks
        Route::middleware('permission:edit-courses')->group(function () {
            Route::post('/courses/{course}/tags', [CourseController::class, 'attachTags']);
            Route::put('/courses/{course}/tags', [CourseController::class, 'syncTags']);
            Route::delete('/courses/{course}/tags', [CourseController::class, 'detachTags']);
        });

        // Enrollment routes with permission checks
        Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'enroll']);
        Route::get('/enrollments/me', [EnrollmentController::class, 'myEnrollments']);
        Route::middleware('permission:view-enrollments')->get('/courses/{course}/enrollments', [EnrollmentController::class, 'getEnrollmentsByCourse']);
        Route::middleware('permission:approve-enrollments')->put('/enrollments/{enrollment}', [EnrollmentController::class, 'updateStatus']);
        Route::middleware('permission:delete-enrollments')->delete('/enrollments/{enrollment}', [EnrollmentController::class, 'destroy']);


        // Statistics routes with permission checks
        Route::middleware('permission:view statistics')->prefix('stats')->group(function () {
            Route::get('/categories', [StatisticsController::class, 'getCategoryStats']);
            Route::get('/tags', [StatisticsController::class, 'getTagStats']);
            Route::get('/courses', [StatisticsController::class, 'getCourseStats']);
        });

        // Roles & Permissions management (admin only)
        Route::middleware('permission:manage roles')->group(function () {
            Route::apiResource('/roles', RoleController::class);
            Route::post('/roles/{role}/permissions', [RoleController::class, 'assignPermissions']);
            Route::delete('/roles/{role}/permissions', [RoleController::class, 'removePermissions']);

            Route::apiResource('/permissions', PermissionController::class);
        });
    });
});
