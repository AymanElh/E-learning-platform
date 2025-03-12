<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\CategoryController;


Route::prefix('v1')->group(function() {
    Route::apiResource('/tags', TagController::class);
    Route::apiResource('/categories', CategoryController::class);
});
