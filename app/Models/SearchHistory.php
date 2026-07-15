<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    protected $fillable = [
        'user_id',
        'query',
        'source',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'user_id');
    }
}
