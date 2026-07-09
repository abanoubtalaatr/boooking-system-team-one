<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;
    protected $table = 'reviews';
    protected $fillable = [
        'user_id',
        'patient_id',
        'comment',
        'rating',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
