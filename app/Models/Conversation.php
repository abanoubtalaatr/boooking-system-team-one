<?php

namespace App\Models;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['patient_id', 'doctor_id', 'status', 'last_message_at'];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function latestMessage(): HasOne
    {
        return $this->HasOne(Message::class)->latestOfMany();
    }

    // هل اليوزر ده (مريض أو دكتور) طرف في المحادثة دي؟
    public function hasParticipant(string $type, int $id): bool
    {
        return match ($type) {
            'patient' => $this->patient_id === $id,
            'doctor' => $this->doctor_id === $id,
            default => false,
        };
    }
}