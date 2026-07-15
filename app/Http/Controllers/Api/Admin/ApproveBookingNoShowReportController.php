<?php

namespace App\Http\Controllers\Api\Admin;

use App\Actions\Booking\ApproveBookingNoShowReportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewBookingNoShowReportRequest;
use App\Http\Resources\BookingNoShowReportResource;
use App\Models\BookingNoShowReport;

class ApproveBookingNoShowReportController extends Controller
{
    public function __construct(private readonly ApproveBookingNoShowReportAction $approveReport) {}

    public function __invoke(
        ReviewBookingNoShowReportRequest $request,
        BookingNoShowReport $bookingNoShowReport,
    ): BookingNoShowReportResource {
        return new BookingNoShowReportResource($this->approveReport->handle(
            $bookingNoShowReport,
            $request->user(),
            $request->validated('review_note'),
        ));
    }
}
