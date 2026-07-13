<x-layouts.dashboard title="إعدادات العمولات" role="admin">
    <div class="breadcrumb">الرئيسية / إعدادات المنصة / العمولات</div>
    <div class="page-head">
        <div>
            <h1 class="page-title">إعدادات عمولات الحجوزات</h1>
            <p class="page-description">حدد نسبة المنصة بشكل مستقل لمدفوعات الفيزا والحجوزات المدفوعة كاش.</p>
        </div>
        <a class="secondary-button" href="{{ route('web.admin.dashboard') }}">العودة للمدفوعات</a>
    </div>

    @if (session('success'))
        <div class="settings-alert settings-alert--success" role="status">{{ session('success') }}</div>
    @endif

    <form class="commission-settings" method="POST" action="{{ route('web.admin.payment-settings.update') }}">
        @csrf
        @method('PUT')

        <section class="commission-settings-grid">
            <article class="commission-setting-card">
                <div class="commission-setting-icon commission-setting-icon--card"><x-ui-icon name="report" /></div>
                <div>
                    <span class="commission-setting-kicker">الدفع الإلكتروني</span>
                    <h2>عمولة الدفع بالفيزا</h2>
                    <p>عند نجاح Paymob تُضاف قيمة الحجز إلى محفظة الطبيب بعد خصم هذه النسبة للمنصة.</p>
                </div>
                <label class="commission-input" for="card_commission_percentage">
                    <span>نسبة المنصة</span>
                    <span class="percentage-control">
                        <input id="card_commission_percentage" name="card_commission_percentage" type="number" min="0" max="100" step="0.01" value="{{ old('card_commission_percentage', $cardCommissionPercentage) }}" required>
                        <b>%</b>
                    </span>
                    @error('card_commission_percentage')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <div class="commission-example">
                    <span>مثال على حجز 500 EGP</span>
                    <strong>المنصة: {{ number_format(500 * (float) old('card_commission_percentage', $cardCommissionPercentage) / 100, 2) }} EGP</strong>
                </div>
            </article>

            <article class="commission-setting-card">
                <div class="commission-setting-icon commission-setting-icon--cash"><x-ui-icon name="clinic" /></div>
                <div>
                    <span class="commission-setting-kicker">الدفع داخل العيادة</span>
                    <h2>عمولة الدفع كاش</h2>
                    <p>عند تأكيد تحصيل الكاش تُخصم هذه النسبة من محفظة الطبيب لصالح المنصة.</p>
                </div>
                <label class="commission-input" for="cash_commission_percentage">
                    <span>نسبة المنصة</span>
                    <span class="percentage-control">
                        <input id="cash_commission_percentage" name="cash_commission_percentage" type="number" min="0" max="100" step="0.01" value="{{ old('cash_commission_percentage', $cashCommissionPercentage) }}" required>
                        <b>%</b>
                    </span>
                    @error('cash_commission_percentage')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <div class="commission-example">
                    <span>مثال على حجز 500 EGP</span>
                    <strong>خصم المنصة: {{ number_format(500 * (float) old('cash_commission_percentage', $cashCommissionPercentage) / 100, 2) }} EGP</strong>
                </div>
            </article>
        </section>

        <aside class="commission-note">
            <x-ui-icon name="shield" />
            <div><strong>كيف تُطبق النسبة؟</strong><p>تُحفظ النسبة داخل عملية الدفع وقت إنشاء Checkout، لذلك تغيير الإعداد لا يغيّر حساب العمليات القديمة.</p></div>
        </aside>

        <div class="settings-actions">
            <button class="primary-button" type="submit">حفظ نسب العمولات</button>
            <a class="secondary-button" href="{{ route('web.admin.payment-settings.edit') }}">إلغاء التعديلات</a>
        </div>
    </form>
</x-layouts.dashboard>
