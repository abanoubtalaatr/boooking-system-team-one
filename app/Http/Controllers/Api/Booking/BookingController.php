<?php

namespace App\Http\Controllers\Api\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService,) {}

    public function store(StoreBookingRequest $request): BookingResource
    {
        $booking = $this->bookingService->create($request->validated());

        return new BookingResource($booking);
    }
}
