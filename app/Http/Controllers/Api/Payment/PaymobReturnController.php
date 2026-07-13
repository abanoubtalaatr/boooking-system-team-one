<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PaymobReturnController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'message' => 'تم استلام طلب الدفع، وجارٍ التحقق من نتيجة العملية. يمكنك متابعة حالة الدفع من تفاصيل الحجز.',
        ], 202);
    }
}
