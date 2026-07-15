<?php

namespace App\Http\Controllers\Api\Admin;

use App\Actions\Booking\RejectBookingNoShowReportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewBookingNoShowReportRequest;
use App\Http\Resources\BookingNoShowReportResource;
use App\Models\BookingNoShowReport;

class RejectBookingNoShowReportController extends Controller
{
    public function __construct(private readonly RejectBookingNoShowReportAction $rejectReport) {}

    public function __invoke(
        ReviewBookingNoShowReportRequest $request,
        BookingNoShowReport $bookingNoShowReport,
    ): BookingNoShowReportResource {
        return new BookingNoShowReportResource($this->rejectReport->handle(
            $bookingNoShowReport,
            $request->user(),
            $request->validated('review_note'),
        ));
    }
}
