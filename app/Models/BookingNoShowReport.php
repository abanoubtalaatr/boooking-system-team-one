<?php
namespace App\Models;

use App\Enums\NoShowReportStatus;
use Database\Factories\BookingNoShowReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingNoShowReport extends Model
{
    /** @use HasFactory<BookingNoShowReportFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'doctor_id',
        'status',
        'reason',
        'reviewed_by',
        'review_note',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'      => NoShowReportStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
