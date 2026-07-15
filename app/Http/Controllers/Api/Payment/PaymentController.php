<?php

namespace App\Http\Controllers\Api\Payment;

use App\Exceptions\PaymentDomainException;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __invoke(Request $request, Payment $payment): PaymentResource
    {
        if ((int) $payment->patient_id !== (int) $request->user('patient')->id) {
            throw new PaymentDomainException('عملية الدفع غير موجودة.', 'payment_not_found', 404);
        }

        return new PaymentResource($payment);
    }
}
