<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Actions\Booking\SubmitBookingNoShowReportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreBookingNoShowReportRequest;
use App\Http\Resources\BookingNoShowReportResource;
use App\Models\Booking;

class StoreBookingNoShowReportController extends Controller
{
    public function __construct(private readonly SubmitBookingNoShowReportAction $submitReport) {}

    public function __invoke(StoreBookingNoShowReportRequest $request, Booking $booking): BookingNoShowReportResource
    {
        $report = $this->submitReport->handle(
            $booking,
            (int) $request->user()->id,
            $request->validated('reason'),
        );

        return new BookingNoShowReportResource($report->load('booking'));
    }
}
