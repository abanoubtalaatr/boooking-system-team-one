<?php

namespace App\Http\Controllers\Api\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\RescheduleBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $bookings = $this->bookingService->listForPatient(
            (int) $request->user('patient')->id,
            $request->query('status')
        );

        return BookingResource::collection($bookings);
    }

    public function show(Request $request, Booking $booking): BookingResource
    {
        $booking = $this->bookingService->showForPatient(
            $booking,
            (int) $request->user('patient')->id
        );

        return new BookingResource($booking);
    }

    public function store(StoreBookingRequest $request): BookingResource
    {
        $booking = $this->bookingService->create(
            $request->validated(),
            (int) $request->user('patient')->id
        );

        return new BookingResource($booking);
    }

    public function cancel(Request $request, Booking $booking): BookingResource
    {
        $booking = $this->bookingService->cancel(
            $booking,
            (int) $request->user('patient')->id
        );

        return new BookingResource($booking);
    }

    public function reschedule(RescheduleBookingRequest $request, Booking $booking): BookingResource {
        $booking = $this->bookingService->reschedule(
            $booking, (int) $request->user('patient')->id,
            $request->validated(),
        );

        return new BookingResource($booking);
    }
}
