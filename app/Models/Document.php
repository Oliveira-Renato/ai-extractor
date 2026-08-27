<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Casts;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'file_path', 'original_filename', 'extracted_json', 'status', 'tokens_used'])]
#[Casts(['extracted_json' => 'array'])]
class Document extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}