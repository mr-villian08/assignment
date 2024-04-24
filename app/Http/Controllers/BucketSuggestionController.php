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
            $buckets = Bucket::select(DB::raw('buckets.id, buckets.name, buckets.volume, IFNULL(bucketSuggestions.volume,0) AS used_volume, (buckets.volume - IFNULL(bucketSuggestions.volume,0)) AS remaining_volume'))
                ->leftJoin(DB::raw('(SELECT bucket_id, SUM(volume) AS volume FROM bucket_suggestions GROUP BY bucket_id) AS bucketSuggestions'), 'bucketSuggestions.bucket_id', '=', 'buckets.id')
                ->whereRaw("(buckets.volume - IFNULL(bucketSuggestions.volume,0)) > 0")
                ->orderByDesc('volume')
                ->orderByDesc('remaining_volume')
                ->get();

            $balls = $request->balls;
            $ballSizes = Ball::whereIn('color', array_keys($balls))->get()->toArray();

            $grandTotalOfBalls = 0;
            $totalBallSizes = [];

            foreach ($ballSizes as $key => $ball) {
                $color = $ball['color'];
                $qty = $balls[$color];
                $ballSizes[$key]['quantity'] = $qty;
                $totalBallSize = number_format($qty * $ball['size'], 2, '.', '');
                $ballSizes[$key]['total_value'] = $totalBallSize;
                $grandTotalOfBalls += $totalBallSize;
            }

            $keys = array_column($ballSizes, 'total_value');
            array_multisort($keys, SORT_DESC, $ballSizes);

            $originalBucket = $buckets;
            $originalBallSizes = $ballSizes;

            $SuggestBucket = [];

            foreach ($ballSizes as  $ballKey => $ball) {
                $totalValue = $ball['total_value'];

                foreach ($buckets as $key => $bucket) {

                    if ($totalValue == $bucket['volume']) {
                        $insertData = ['bucket_id' => $bucket['id'], 'ball_id' => $ball['id'], 'quantity' => $ball['quantity'], 'volume' => $totalValue];
                        $SuggestBucket[] = $insertData;
                        BucketSuggestion::create($insertData);
                        $SuggestBucket[] = array_merge($insertData, ['note' => 'main if']);
                        $ballSizes[$ballKey]['quantity'] = $ball['quantity'] = 0;
                        unset($buckets[$key]);
                        break;
                    } elseif ($totalValue < $bucket['volume']) {
                        $buckets[$key]['volume'] -= $totalValue;
                        $insertData = ['bucket_id' => $bucket['id'], 'ball_id' => $ball['id'], 'quantity' => $ball['quantity'], 'volume' => $totalValue];
                        $SuggestBucket[] = array_merge($insertData, ['note' => '1st else if']);
                        BucketSuggestion::create($insertData);
                        $ballSizes[$ballKey]['quantity'] = $ball['quantity'] = 0;

                        break;
                    } elseif ($totalValue > $bucket['volume']) {
                        $occupiedQty = floor($bucket['volume'] / $ball['size']);
                        if ($occupiedQty > 0) {
                            $occupiedTotalValue = $occupiedQty * $ball['size'];

                            $insertData = ['bucket_id' => $bucket['id'], 'ball_id' => $ball['id'], 'quantity' => $occupiedQty, 'volume' => $occupiedTotalValue];
                            BucketSuggestion::create($insertData);
                            $SuggestBucket[] = array_merge($insertData, ['note' => 'last else if']);
                            $ballSizes[$ballKey]['quantity'] = $ball['quantity'] = $ball['quantity'] - $occupiedQty;

                            $buckets[$key]['volume'] -= $occupiedTotalValue;
                            $totalValue = number_format($ball['quantity'] * $ball['size'], 2, '.', '');
                        }
                    }
                }
            }

            $getWarningMessage = $this->warningMessage($ballSizes);

            return $this->success("Bucket suggestion ha been saved successfully.", [
                "balls" => $balls,
                "originalBucket" => $originalBucket,
                "originalBallSizes" => $originalBallSizes,
                "ballSizes" => $ballSizes,
                "totalBallSizes" => $totalBallSizes,
                "SuggestBucket" => $SuggestBucket,
            ]);
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
                $ballStr = Str::plural('ball', $bucket['qty']);
                $color = ucfirst($bucket['color']);
                $listArr[$bucket['name']]['balls'][] = "{$bucket['qty']} {$color} {$ballStr}";
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

        return '';
    }
}
