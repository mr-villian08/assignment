<?php

namespace App\Http\Controllers;

use App\Models\Ball;
use App\Models\BucketSuggestion;
use App\Traits\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BallController extends Controller
{
    use Reply;
    // ? ********************************************************** Save the balls ********************************************************** */
    public function save(Request $request)
    {
        $validations = Validator::make($request->all(), [
            "color" => "required",
            "size" => "required"
        ]);

        if ($validations->errors()->all()) {
            return $this->failed($validations->errors()->first());
        }

        $save = Ball::create($request->all());
        if ($save) {
            BucketSuggestion::truncate();
            return $this->success("Ball has been created successfully.");
        }

        return $this->failed("Unable to create the ball. Try again!");
    }

    // ? ********************************************************** Show the balls ********************************************************** */
    public function show()
    {
        $balls = Ball::all();
        return $this->success("Here are all the balls.", $balls);
    }
}
