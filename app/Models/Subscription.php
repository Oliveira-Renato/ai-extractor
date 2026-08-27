<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Casts;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'lemon_squeezy_id', 'status', 'current_period_end'])]
#[Casts(['current_period_end' => 'datetime'])]
class Subscription extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}