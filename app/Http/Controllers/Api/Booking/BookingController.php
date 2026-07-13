<?php

namespace App\Http\Controllers\Api\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\CancelBookingRequest;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingService;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    // Inject the BookingService into the controller
    public function __construct(protected BookingService $bookingService,) {}

    // Create a new booking
    public function store(StoreBookingRequest $request): BookingResource
    {
        $booking = $this->bookingService->create($request->validated());

        return new BookingResource($booking);
    }

    // Cancel a booking
    public function cancel(CancelBookingRequest $request, Booking $booking): BookingResource {
        $booking = $this->bookingService->cancel($booking);

        return new BookingResource($booking);
    }
}
