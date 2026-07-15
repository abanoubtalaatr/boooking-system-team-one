<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Actions\Payment\ListDashboardPaymentsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\DashboardPaymentIndexRequest;
use App\Http\Resources\DashboardPaymentResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoctorPaymentIndexController extends Controller
{
    public function __construct(private readonly ListDashboardPaymentsAction $payments) {}

    public function __invoke(DashboardPaymentIndexRequest $request): AnonymousResourceCollection
    {
        return DashboardPaymentResource::collection(
            $this->payments->handle($request->validated(), (int) $request->user()->id)
        );
    }
}
