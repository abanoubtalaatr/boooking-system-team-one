<?php

namespace App\Http\Controllers\Web;

use App\Actions\Payment\UpdatePaymentCommissionSettingsAction;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePaymentCommissionSettingsRequest;
use App\Services\Payments\PlatformCommissionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AdminPaymentSettingsController extends Controller
{
    public function __construct(
        private readonly PlatformCommissionService $commission,
        private readonly UpdatePaymentCommissionSettingsAction $updateSettings,
    ) {}

    public function edit(): View
    {
        return view('admin.settings', [
            'cardCommissionPercentage' => $this->commission->formattedPercentage(PaymentMethod::Card),
            'cashCommissionPercentage' => $this->commission->formattedPercentage(PaymentMethod::Cash),
        ]);
    }

    public function update(UpdatePaymentCommissionSettingsRequest $request): RedirectResponse
    {
        $this->updateSettings->handle($request->validated());

        return redirect()->route('admin.settings')
            ->with('success', 'تم تحديث إعدادات المنصة بنجاح.');
    }
}
