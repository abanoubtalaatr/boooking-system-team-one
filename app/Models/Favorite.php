<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    //
    protected $table = 'favorites';

    protected $fillable = [
        'user_id',
        'doctor_id',
    ];

    public function user()
    {
        return $this->belongsTo(Patient::class, 'user_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
