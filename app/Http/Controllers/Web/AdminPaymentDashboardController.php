<?php

namespace App\Http\Controllers\Web;

use App\Actions\Payment\GetAdminPaymentDashboardSummaryAction;
use App\Actions\Payment\ListDashboardPaymentsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\DashboardPaymentIndexRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminPaymentDashboardController extends Controller
{
    public function __construct(
        private readonly ListDashboardPaymentsAction $payments,
        private readonly GetAdminPaymentDashboardSummaryAction $summary,
    ) {}

    public function __invoke(DashboardPaymentIndexRequest $request): View
    {
        return view('admin.dashboard', [
            'payments' => $this->payments->handle($request->validated()),
            'summary' => $this->summary->handle(),
            'doctors' => User::query()
                ->where('role', 'doctor')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}
