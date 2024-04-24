<?php

namespace App\Http\Controllers;

use App\Models\Bucket;
use App\Models\BucketSuggestion;
use App\Traits\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BucketController extends Controller
{
    use Reply;
    // ? ********************************************************** First Page ********************************************************** */
    public function index()
    {
        return view('index');
    }

    // ? ********************************************************** Save the bucket ********************************************************** */
    public function save(Request $request)
    {
        $validations = Validator::make($request->all(), [
            "name" => "required|unique:buckets,name",
            "volume" => "required"
        ]);

        if ($validations->errors()->all()) {
            return $this->failed($validations->errors()->first());
        }
        $save = Bucket::create($request->all());
        if ($save) {
            BucketSuggestion::truncate();
            return $this->success("Bucket has been saved successfully.");
        }

        return $this->failed("Unable to create the bucket. Try again!");
    }
}
