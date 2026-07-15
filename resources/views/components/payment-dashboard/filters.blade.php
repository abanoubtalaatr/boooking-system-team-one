@props(['action', 'showDoctor' => false, 'doctors' => collect()])

@php
    $statuses = [
        'pending' => 'قيد الانتظار',
        'initiated' => 'بدأ الدفع',
        'pending_verification' => 'بانتظار التحقق',
        'cash_due' => 'كاش مستحق',
        'cash_collected' => 'تم تحصيل الكاش',
        'succeeded' => 'ناجحة',
        'paid' => 'مدفوعة',
        'failed' => 'فشلت',
        'refund_pending' => 'استرداد قيد التنفيذ',
        'refunded' => 'تم الاسترداد',
        'voided' => 'ملغاة',
    ];
@endphp

<form class="payment-filters" method="GET" action="{{ $action }}">
    @if ($showDoctor)
        <label class="filter-field">
            <span>الطبيب</span>
            <select name="doctor_id">
                <option value="">كل الأطباء</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected((string) request('doctor_id') === (string) $doctor->id)>{{ $doctor->name }}</option>
                @endforeach
            </select>
        </label>
    @endif

    <label class="filter-field">
        <span>طريقة الدفع</span>
        <select name="method">
            <option value="">الكل</option>
            <option value="card" @selected(request('method') === 'card')>فيزا / بطاقة</option>
            <option value="cash" @selected(request('method') === 'cash')>كاش في العيادة</option>
        </select>
    </label>

    <label class="filter-field">
        <span>حالة العملية</span>
        <select name="status">
            <option value="">كل الحالات</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>

    <label class="filter-field">
        <span>من تاريخ</span>
        <input name="date_from" type="date" value="{{ request('date_from') }}">
    </label>

    <label class="filter-field">
        <span>إلى تاريخ</span>
        <input name="date_to" type="date" value="{{ request('date_to') }}">
    </label>

    <div class="filter-actions">
        <button class="primary-button" type="submit">تطبيق الفلاتر</button>
        <a class="secondary-button" href="{{ $action }}">إعادة ضبط</a>
    </div>
</form>

@if ($errors->any())
    <div class="filter-errors" role="alert">تحقق من قيم الفلاتر والتواريخ المدخلة.</div>
@endif
