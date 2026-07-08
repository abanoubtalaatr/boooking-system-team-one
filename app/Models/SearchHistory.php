<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
// use App\Models\Doctor;
class SearchHistory extends Model
{
    protected $fillable = [
        'user_id',
        'doctor_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // TODO: add the doctor relationship 
    // public function doctor()
    // {
    //     return $this->belongsTo(Doctor::class);
    // }
}
