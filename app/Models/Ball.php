<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ball extends Model
{
    use HasFactory;

    protected $fillable = [
        "color",
        "size"
    ];

    // ? ********************************************************** Getters and setters ********************************************************** */
    // set the name
    protected function color(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => ucfirst($value),
        );
    }

    // ? ********************************************************** Relations ********************************************************** */
    // with bucket suggestions
    public function bucketSuggestions(): HasMany
    {
        return $this->hasMany(BucketSuggestion::class);
    }
}
