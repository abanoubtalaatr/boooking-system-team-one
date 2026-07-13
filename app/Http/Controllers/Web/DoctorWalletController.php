<?php

namespace App\Http\Controllers\Web;

use App\Actions\Wallet\CreateWalletWithdrawalAction;
use App\Actions\Wallet\GetDoctorWalletPageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\StoreWalletWithdrawalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DoctorWalletController extends Controller
{
    public function __construct(
        private readonly GetDoctorWalletPageAction $getWalletPage,
        private readonly CreateWalletWithdrawalAction $createWithdrawal,
    ) {}

    public function index(Request $request): View
    {
        return view('doctor.wallet', [
            ...$this->getWalletPage->handle($request->user()),
            'withdrawalRequestKey' => (string) Str::uuid(),
        ]);
    }

    public function store(StoreWalletWithdrawalRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->createWithdrawal->handle(
            $request->user(),
            (string) $validated['amount'],
            (string) $validated['idempotency_key'],
        );

        return to_route('web.doctor.wallet.index')
            ->with('success', 'تم إرسال طلب السحب إلى الإدارة للمراجعة.');
    }
}
