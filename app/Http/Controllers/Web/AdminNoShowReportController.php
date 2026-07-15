<?php

namespace App\Http\Controllers\Web;

use App\Actions\Booking\ApproveBookingNoShowReportAction;
use App\Actions\Booking\GetAdminNoShowDashboardAction;
use App\Actions\Booking\RejectBookingNoShowReportAction;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewBookingNoShowReportRequest;
use App\Http\Requests\NoShow\AdminNoShowReportIndexRequest;
use App\Models\BookingNoShowReport;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminNoShowReportController extends Controller
{
    public function __construct(
        private readonly GetAdminNoShowDashboardAction $dashboard,
        private readonly ApproveBookingNoShowReportAction $approveReport,
        private readonly RejectBookingNoShowReportAction $rejectReport,
    ) {}

    public function index(AdminNoShowReportIndexRequest $request): View
    {
        return view('admin.no-show-reports', [
            ...$this->dashboard->handle($request->validated()),
            'doctors' => User::query()
                ->where('role', UserRole::Doctor)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function approve(
        ReviewBookingNoShowReportRequest $request,
        BookingNoShowReport $bookingNoShowReport,
    ): RedirectResponse {
        $this->approveReport->handle(
            $bookingNoShowReport,
            $request->user(),
            $request->validated('review_note'),
        );

        return back()->with('success', 'تم قبول البلاغ وتسوية الحجز ماليًا.');
    }

    public function reject(
        ReviewBookingNoShowReportRequest $request,
        BookingNoShowReport $bookingNoShowReport,
    ): RedirectResponse {
        $this->rejectReport->handle(
            $bookingNoShowReport,
            $request->user(),
            $request->validated('review_note'),
        );

        return back()->with('success', 'تم رفض البلاغ دون تغيير حالة الحجز أو الرصيد.');
    }
}
