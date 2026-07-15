<x-layouts.dashboard title="أطباء {{ $patient->name }} المفضلون" role="admin">
    <div class="breadcrumb">الرئيسية / المفضلة لدى المرضى / {{ $patient->name }}</div>
    <div class="page-head">
        <div>
            <h1 class="page-title">الأطباء المفضلون لـ {{ $patient->name }}</h1>
            <p class="page-description">قائمة كل الأطباء الذين أضافهم هذا المريض إلى المفضلة ({{ $favorites->total() }}).</p>
        </div>
        <div>

            <a class="primary-button text-white bg-blue-500 hover:bg-blue-600" href="{{ route('admin.patient-favorites') }}">→ رجوع للقائمة</a>
        </div>
    </div>

    <article class="panel">
        <div class="panel-head">
            <h2 class="panel-title">الأطباء المفضلون</h2>
        </div>

        @forelse ($favorites as $favorite)
            @php $doctor = $favorite->doctor; $profile = $doctor?->doctorProfile; @endphp
            @if ($doctor)
                <a class="schedule-item" href="#">
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
                </a>
            @else
                <div class="schedule-item">
                    <span class="avatar">د</span>
                    <span class="schedule-copy">
                        <strong>طبيب محذوف</strong>
                        <small>لم يعد هذا الطبيب متاحاً</small>
                    </span>
                </div>
            @endif
        @empty
            <p class="field-hint">لم يضف هذا المريض أي طبيب إلى المفضلة بعد.</p>
        @endforelse
    </article>

    <x-admin.paginator
        :paginator="$favorites"
        item-label="طبيب"
    />
</x-layouts.dashboard>
