<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilitySlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id', 'day', 'start_time', 'end_time', 'is_booked',
    ];

    protected $casts = [
        'day'       => 'date:Y-m-d',
        'is_booked' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}