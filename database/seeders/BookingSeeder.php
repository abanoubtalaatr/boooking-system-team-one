<?php

namespace Database\Seeders;

use App\Models\AvailabilitySlot;
use App\Models\Booking;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        Booking::factory(20)
            ->create()
            ->each(function (Booking $booking) {
                AvailabilitySlot::whereKey($booking->availability_slot_id)
                    ->update([
                        'is_booked' => true,
                    ]);
            });
    }
}
