<?php

namespace App\Http\Controllers\Web;

use App\Actions\Doctor\GetDoctorDashboardAction;
use App\Actions\Payment\ListDashboardPaymentsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\DashboardPaymentIndexRequest;
use Illuminate\Contracts\View\View;

class DoctorPaymentDashboardController extends Controller
{
    public function __construct(
        private readonly ListDashboardPaymentsAction $payments,
        private readonly GetDoctorDashboardAction $dashboard,
    ) {}

    public function __invoke(DashboardPaymentIndexRequest $request): View
    {
        $doctor = $request->user();

        return view('doctor.dashboard', [
            'payments' => $this->payments->handle($request->validated(), (int) $doctor->id),
            'dashboard' => $this->dashboard->handle($doctor),
        ]);
    }
}
