<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'original_name',
    'storage_path',
    'mime_type',
    'size_bytes',
])]
class Image extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
