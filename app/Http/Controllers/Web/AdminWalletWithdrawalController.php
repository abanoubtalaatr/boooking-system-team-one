<?php

namespace App\Http\Controllers\Web;

use App\Actions\Wallet\CancelWalletWithdrawalAction;
use App\Actions\Wallet\CompleteWalletWithdrawalAction;
use App\Actions\Wallet\ListAdminWithdrawalsAction;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\AdminWithdrawalIndexRequest;
use App\Http\Requests\Wallet\CancelWalletWithdrawalRequest;
use App\Models\User;
use App\Models\WalletWithdrawal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminWalletWithdrawalController extends Controller
{
    public function __construct(
        private readonly ListAdminWithdrawalsAction $listWithdrawals,
        private readonly CompleteWalletWithdrawalAction $completeWithdrawal,
        private readonly CancelWalletWithdrawalAction $cancelWithdrawal,
    ) {}

    public function index(AdminWithdrawalIndexRequest $request): View
    {
        return view('admin.withdrawals', [
            ...$this->listWithdrawals->handle($request->validated()),
            'doctors' => User::query()
                ->where('role', UserRole::Doctor)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function complete(Request $request, WalletWithdrawal $walletWithdrawal): RedirectResponse
    {
        $this->completeWithdrawal->handle($walletWithdrawal, $request->user());

        return back()->with('success', 'تم قبول طلب السحب وخصم المبلغ من المحفظة.');
    }

    public function cancel(CancelWalletWithdrawalRequest $request, WalletWithdrawal $walletWithdrawal): RedirectResponse
    {
        $this->cancelWithdrawal->handle(
            $walletWithdrawal,
            $request->user(),
            (string) $request->validated('rejection_reason'),
        );

        return back()->with('success', 'تم رفض طلب السحب دون خصم أي مبلغ من المحفظة.');
    }
}
