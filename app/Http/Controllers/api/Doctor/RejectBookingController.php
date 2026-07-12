<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\DoctorService;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

class RejectBookingController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly DoctorService $doctors)
    {
    }

    public function __invoke(Booking $booking): JsonResponse
    {
        $this->authorize("update", $booking);
        $booking = $this->doctors->rejectBooking($booking);

        return $this->apiResponse($booking, "Booking rejected.");
    }
}
