<?php

namespace App\Models;

use App\Enums\MessageStatus;
use App\Enums\MessageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Message extends Model implements HasMedia
{
    use  HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = ["conversation_id", "sender_id", "type", "content", "status"];

    protected $casts = [
        "type" => MessageType::class,
        "status" => MessageStatus::class,
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, "sender_id");
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection("image");
        $this->addMediaCollection("file");
        $this->addMediaCollection("voice");
    }
}
