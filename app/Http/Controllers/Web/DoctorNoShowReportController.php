<?php

namespace App\Http\Controllers\Web;

use App\Actions\Booking\GetDoctorNoShowDashboardAction;
use App\Actions\Booking\SubmitBookingNoShowReportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreBookingNoShowReportRequest;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorNoShowReportController extends Controller
{
    public function __construct(
        private readonly GetDoctorNoShowDashboardAction $dashboard,
        private readonly SubmitBookingNoShowReportAction $submitReport,
    ) {}

    public function index(Request $request): View
    {
        return view('doctor.no-show-reports', [
            ...$this->dashboard->handle((int) $request->user()->id),
        ]);
    }

    public function store(StoreBookingNoShowReportRequest $request, Booking $booking): RedirectResponse
    {
        $this->submitReport->handle(
            $booking,
            (int) $request->user()->id,
            (string) $request->validated('reason'),
        );

        return back()->with('success', 'تم إرسال بلاغ عدم الحضور إلى الإدارة للمراجعة.');
    }
}
