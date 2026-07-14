<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
//use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Message extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = ['conversation_id', 'sender_id', 'sender_type', 'type', 'body', 'read_at'];


    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];

    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    // sender ممكن يكون Patient أو User (doctor)
    public function sender(): MorphTo
    {
        return $this->morphTo();
    }

    // كل رسالة بيبقى ليها مرفق واحد بس (singleFile) - كافي للـ MVP
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachment')->singleFile();
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('attachment') ?: null;
    }
}