<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    public function index(Request $request)
    {
        $data = $this->bookingService->index(
            $request->only([
                'search',
                'status',
                'doctor',
                'from',
                'to',
            ])
        );

        return view('admin.bookings.index', [
            'bookings' => $data['bookings'],
            'stats' => $data['stats'],
            'statuses' => BookingStatus::cases(),
        ]);
    }

    public function show(Booking $booking): View
    {
        $booking = $this->bookingService->show($booking);

        return view('admin.bookings.show', compact('booking'));
    }
}
