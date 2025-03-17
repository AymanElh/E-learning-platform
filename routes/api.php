<?php

use App\Http\Controllers\Api\V1\CourseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\CategoryController;


Route::prefix('v1')->group(function() {
    Route::apiResource('/tags', TagController::class);
    Route::apiResource('/categories', CategoryController::class);
    Route::apiResource('/courses', CourseController::class);

    Route::post('/courses/{course}/tags', [CourseController::class, 'attachTags']);
    Route::put('/courses/{course}/tags', [CourseController::class, 'syncTags']);
    Route::delete('/courses/{course}/tags', [CourseController::class, 'detachTags']);
});
