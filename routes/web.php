<?php

use App\Http\Controllers\BallController;
use App\Http\Controllers\BucketController;
use App\Http\Controllers\BucketSuggestionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BucketController::class, "index"]);

// ? ********************************************************** Buckets Routes ********************************************************** */
Route::prefix("/buckets")->group(function () {
    Route::post("/", [BucketController::class, "save"]);
});


// ? ********************************************************** Balls Routes ********************************************************** */
Route::prefix("/balls")->group(function () {
    Route::post("/", [BallController::class, "save"]);
    Route::get("/", [BallController::class, "show"]);
});

// ? ********************************************************** Bucket Suggestions Routes ********************************************************** */
Route::prefix("/bucket-suggestions")->group(function () {
    Route::post("/", [BucketSuggestionController::class, "save"]);
    Route::get("/", [BucketSuggestionController::class, "show"]);
});
