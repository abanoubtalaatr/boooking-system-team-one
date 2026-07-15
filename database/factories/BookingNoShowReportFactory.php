<?php

namespace Database\Factories;

use App\Enums\NoShowReportStatus;
use App\Models\Booking;
use App\Models\BookingNoShowReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingNoShowReport>
 */
class BookingNoShowReportFactory extends Factory
{
    protected $model = BookingNoShowReport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'doctor_id' => fn (array $attributes) => Booking::query()->findOrFail($attributes['booking_id'])->doctor_id,
            'status' => NoShowReportStatus::PendingReview,
            'reason' => fake()->sentence(),
        ];
    }
}
