<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        "Language" => "PHP",
        "Framework" => "Laravel",
        "Version" => Application::VERSION
    ]);
});
