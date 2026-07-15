<x-layouts.dashboard title="أطباء {{ $patient->name }} المفضلون" role="admin">
    <div class="breadcrumb">الرئيسية / المفضلة لدى المرضى / {{ $patient->name }}</div>
    <div class="page-head">
        <div>
            <h1 class="page-title">الأطباء المفضلون لـ {{ $patient->name }}</h1>
            <p class="page-description">قائمة كل الأطباء الذين أضافهم هذا المريض إلى المفضلة ({{ $favorites->total() }}).</p>
        </div>
        <a class="primary-button" href="{{ route('admin.patient-favorites') }}">→ رجوع للقائمة</a>
    </div>

    <article class="panel">
        <div class="panel-head">
            <h2 class="panel-title">الأطباء المفضلون</h2>
        </div>

        @forelse ($favorites as $favorite)
            @php $doctor = $favorite->doctor; $profile = $doctor?->doctorProfile; @endphp
            <div class="schedule-item">
                <span class="avatar">{{ mb_substr($doctor->name ?? 'د', 0, 1) }}</span>
                <span class="schedule-copy">
                    <strong>{{ $doctor->name ?? 'طبيب محذوف' }}</strong>
                    <small>
                        {{ $profile?->specialty?->name ?? 'بدون تخصص' }}
                        @if ($profile?->hospital?->name)
                            · {{ $profile->hospital->name }}
                        @endif
                    </small>
                </span>
                @if (! is_null($profile?->price))
                    <span class="status">{{ number_format((float) $profile->price) }} ج.م</span>
                @endif
            </div>
        @empty
            <p class="field-hint">لم يضف هذا المريض أي طبيب إلى المفضلة بعد.</p>
        @endforelse
    </article>

    <div class="pagination-wrap">
        {{ $favorites->links() }}
    </div>
</x-layouts.dashboard>
