<?php

namespace App\Http\Controllers;

use App\Models\Ball;
use App\Models\Bucket;
use App\Models\BucketSuggestion;
use App\Traits\Reply;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BucketSuggestionController extends Controller
{
    use Reply;
    // ? ********************************************************** Save ********************************************************** */
    public function save(Request $request)
    {
        try {
            // $buckets = Bucket::select(DB::raw('buckets.id, buckets.name, buckets.volume AS full_volume, IFNULL(bucketSuggestions.volume,0) AS used_volume, (buckets.volume - IFNULL(bucketSuggestions.volume,0)) AS volume'))
            //     ->leftJoin(DB::raw('(SELECT bucket_id, SUM(volume) AS volume FROM bucket_suggestions GROUP BY bucket_id) AS bucketSuggestions'), 'bucketSuggestions.bucket_id', '=', 'buckets.id')
            //     ->whereRaw("(buckets.volume - IFNULL(bucketSuggestions.volume,0)) > 0")
            //     ->orderBy('full_volume', "DESC")
            //     ->orderBy('volume', "DESC")
            //     ->get()
            //     ->toArray();
            $buckets = Bucket::withSum("bucketSuggestions as used_volume", "volume")->orderBy("volume", "DESC")->get()->each(function ($bucket) {
                $bucket->used_volume = $bucket->used_volume ?? 0;
                $bucket->remaining_volume = $bucket->volume - $bucket->used_volume;
            })->where("remaining_volume", ">", 0)->sortByDesc('remaining_volume');

            // $buckets = Bucket::select(DB::raw('buckets.id, buckets.name, buckets.volume AS full_volume, IFNULL(bucketSuggestions.volume,0) AS used_volume, (buckets.volume - IFNULL(bucketSuggestions.volume,0)) AS volume'))
            //     ->leftJoin(DB::raw('(SELECT bucket_id, SUM(volume) AS volume FROM bucket_suggestions GROUP BY bucket_id) AS bucketSuggestions'), 'bucketSuggestions.bucket_id', '=', 'buckets.id')
            //     ->whereRaw("(buckets.volume - IFNULL(bucketSuggestions.volume,0)) > 0")
            //     ->orderBy('full_volume', "DESC")
            //     ->orderBy('volume', "DESC")
            //     ->get()
            //     ->toArray();


            $requestedBalls = $request->balls;
            $balls = Ball::get()->each(function ($ball) use ($requestedBalls) {
                $ball->quantity = $requestedBalls[$ball->color];
                $ball->purchasing_balls = number_format($requestedBalls[$ball->color] * $ball->size, 2);
            });
            return $this->success("Here is the data", $balls);
            // ->map(function ($ball) use ($requestedBalls) {
            //     return (object) [
            //         'balls' => $ball,
            //         'totalNumberOfBalls' =>
            //     ];
            // });
            // $gradTotalOfBalls = 0;
            // $totalBallSizes = [];

            // foreach ($ballSizes as $key => $ball) {
            //     $color = $ball['color'];
            //     $qty = $balls[$color];
            //     $ballSizes[$key]['quantity'] = $qty;
            //     $totalBallSize = number_format($qty * $ball['size'], 2, '.', '');
            //     $ballSizes[$key]['total_value'] = $totalBallSize;
            //     $gradTotalOfBalls += $totalBallSize;
            // }
            // return $this->success("Here is the data", $ballSizes);
            // echo '<pre>';
            // print_r($ballSizes);
            // die;
            // echo '</pre>';
            // $keys = array_column($ballSizes, 'total_value');
            // array_multisort($keys, SORT_DESC, $ballSizes);

            // $originalBucket = $buckets;
            // $originalBallSizes = $ballSizes;

            // $SuggestBucket = [];
            // foreach ($ballSizes as  $ballKey => $ball) {
            //     $total_value = $ball['total_value'];

            //     foreach ($buckets as $key => $bucket) {

            //         if ($total_value == $bucket['volume']) {
            //             $insertData = ['bucket_id' => $bucket['id'], 'ball_id' => $ball['id'], 'quantity' => $ball['quantity'], 'volume' => $total_value];
            //             $SuggestBucket[] = $insertData;
            //             BucketSuggestion::create($insertData);
            //             $SuggestBucket[] = array_merge($insertData, ['note' => 'main if']);
            //             $ballSizes[$ballKey]['quantity'] = $ball['quantity'] = 0;
            //             unset($buckets[$key]);
            //             break;
            //         } elseif ($total_value < $bucket['volume']) {
            //             $buckets[$key]['volume'] -= $total_value;
            //             $insertData = ['bucket_id' => $bucket['id'], 'ball_id' => $ball['id'], 'quantity' => $ball['quantity'], 'volume' => $total_value];
            //             $SuggestBucket[] = array_merge($insertData, ['note' => '1st else if']);
            //             BucketSuggestion::create($insertData);
            //             $ballSizes[$ballKey]['quantity'] = $ball['quantity'] = 0;

            //             break;
            //         } elseif ($total_value > $bucket['volume']) {
            //             $occupiedQty = floor($bucket['volume'] / $ball['size']);
            //             if ($occupiedQty > 0) {
            //                 $occupiedTotalValue = $occupiedQty * $ball['size'];
            //                 $insertData = ['bucket_id' => $bucket['id'], 'ball_id' => $ball['id'], 'quantity' => $occupiedQty, 'volume' => $occupiedTotalValue];
            //                 BucketSuggestion::create($insertData);
            //                 $SuggestBucket[] = array_merge($insertData, ['note' => 'last else if']);
            //                 $ballSizes[$ballKey]['quantity'] = $ball['quantity'] = $ball['quantity'] - $occupiedQty;
            //                 $buckets[$key]['volume'] -= $occupiedTotalValue;
            //                 $total_value = number_format($ball['quantity'] * $ball['size'], 2, '.', '');
            //             }
            //         }
            //     }
            // }

            // $getWarningMessage = $this->warningMessage($ballSizes);

            // return $this->success($getWarningMessage, [
            //     "balls" => $balls,
            //     "originalBucket" => $originalBucket,
            //     "originalBallSizes" => $originalBallSizes,
            //     "ballSizes" => $ballSizes,
            //     "totalBallSizes" => $totalBallSizes,
            //     "SuggestBucket" => $SuggestBucket,
            // ]);
        } catch (Exception $e) {
            return $this->failed($e->getMessage());
        }
    }

    // ? ********************************************************** Show ********************************************************** */
    public function show(Request $request)
    {
        $buckets = Bucket::select(DB::raw('buckets.id, buckets.name, buckets.volume, IFNULL(bucketSuggestions.volume,0) AS used_volume
        , (buckets.volume - IFNULL(bucketSuggestions.volume,0)) AS remaining_volume, balls.color, balls.size, bucketSuggestions.quantity'))
            ->leftJoin(DB::raw('(SELECT bucket_id, ball_id, SUM(volume) AS volume, SUM(quantity) AS quantity  FROM `bucket_suggestions` GROUP BY bucket_id, ball_id) AS bucketSuggestions'), 'bucketSuggestions.bucket_id', '=', 'buckets.id')
            ->join('balls', 'bucketSuggestions.ball_id', '=', 'balls.id')
            ->orderBy('buckets.id', "ASC")
            ->get();

        if ($buckets->isEmpty()) {
            $buckets = [];
        } else {
            $listArr = [];
            foreach ($buckets as $bucket) {
                $ballStr = Str::plural('ball', $bucket['quantity']);
                $color = ucfirst($bucket['color']);
                $listArr[$bucket['name']]['balls'][] = "{$bucket['quantity']} {$color} {$ballStr}";
                $listArr[$bucket['name']]['used_volume'][] = $bucket['used_volume'];
                $listArr[$bucket['name']]['volume'] = $bucket['volume'];
            }

            $finalListArr = [];
            foreach ($listArr as  $bucketName => $Bucketlist) {
                $bucketName = strtoupper($bucketName);
                $allBallsString = implode(" and ", $Bucketlist['balls']);
                $totalUsedVolume = array_sum($Bucketlist['used_volume']);
                $remainingVolume = number_format($Bucketlist['volume'] - $totalUsedVolume, 2, '.', '');
                $finalListArr[] = "Bucket {$bucketName}: <strong>Place {$allBallsString}</strong> and remaining volume is {$remainingVolume}";
            }
        }

        return $this->success("Here is the data", [
            "data" => $finalListArr,
            "buckets" => $buckets,
            "listArr" => $listArr,
        ]);
    }

    // ? ********************************************************** Show the warning message ********************************************************** */
    private function warningMessage($ballSizes)
    {
        $finalMessage = "";
        $message = "";
        $messages = [];
        $numberOfBallNotPlace = 0;
        foreach ($ballSizes as $ball) {
            $ballStr = Str::plural('ball', $ball['quantity']);
            $color = ucfirst($ball['color']);
            if ($ball['quantity'] > 0) {
                $message .= " {$ball['quantity']} {$color} {$ballStr} and";
                $numberOfBallNotPlace++;
            }
        }

        if ($message != '') {
            return trim($message, "and") . Str::plural('is', $numberOfBallNotPlace)
                . " not place due to either all bucket fulls or not required space is empty to any bucket.";
        }

        return 'Balls has been added to the bucket successfully.';
    }
}
