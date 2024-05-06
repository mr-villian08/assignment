<?php

use App\Http\Controllers\BucketSuggestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// ? ********************************************************** Bucket Suggestions Routes ********************************************************** */
Route::prefix("/bucket-suggestions")->group(function () {
    Route::post("/", [BucketSuggestionController::class, "save"]);
    Route::get("/", [BucketSuggestionController::class, "show"]);
});
