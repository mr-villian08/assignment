<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BucketSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        "bucket_id",
        "ball_id",
        "quantity",
        "volume",
    ];

    // ? ********************************************************** Relations ********************************************************** */
    // with bucket
    public function bucket(): BelongsTo
    {
        return $this->belongsTo(Bucket::class);
    }

    // with ball
    public function ball(): BelongsTo
    {
        return $this->belongsTo(Ball::class);
    }
}
