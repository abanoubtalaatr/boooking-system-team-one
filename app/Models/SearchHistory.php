<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class SearchHistory extends Model
{
    protected $fillable = [
        'user_id',
        'query',
        'source',
    ];

    // Relationships: 
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
