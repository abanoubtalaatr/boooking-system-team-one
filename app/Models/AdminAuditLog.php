<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAuditLog extends Model
{
    /** @use HasFactory<\\Database\\Factories\\AdminAuditLogFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['actor_id', 'target_id', 'action', 'before', 'after', 'ip_address', 'user_agent'];

    protected function casts(): array
    {
        return ['before' => 'array', 'after' => 'array', 'created_at' => 'datetime'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id')->withTrashed();
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id')->withTrashed();
    }
}
