@php
    $sourceLabels = [
        'search' => 'بحث',
        'chat' => 'محادثة',
        'favorite' => 'مفضلة',
    ];
@endphp

<x-layouts.dashboard title="سجل بحث {{ $patient->name }}" role="admin">
    <div class="breadcrumb">الرئيسية / سجل البحث / {{ $patient->name }}</div>
    <div class="page-head">
        <div>
            <h1 class="page-title">سجل بحث {{ $patient->name }}</h1>
            <p class="page-description">كل عمليات البحث التي سجّلها هذا المريض ({{ $searchHistories->total() }}).</p>
        </div>
        <div>
            <a class="primary-button text-white bg-blue-500 hover:bg-blue-600" href="{{ route('admin.search-history') }}">→ رجوع للقائمة</a>
        </div>
    </div>

    <article class="panel">
        <div class="panel-head">
            <h2 class="panel-title">عمليات البحث</h2>
        </div>

        @forelse ($searchHistories as $entry)
            <div class="schedule-item">
                <span class="avatar"><x-ui-icon name="search" /></span>
                <span class="schedule-copy">
                    <strong>{{ $entry->query }}</strong>
                    <small>{{ $entry->updated_at?->format('Y/m/d H:i') ?? '—' }}</small>
                </span>
                <span class="status">{{ $sourceLabels[$entry->source] ?? ($entry->source ?? 'عام') }}</span>
            </div>
        @empty
            <p class="field-hint">لم يسجّل هذا المريض أي عملية بحث بعد.</p>
        @endforelse
    </article>

    <x-admin.paginator
        :paginator="$searchHistories"
        item-label="عملية بحث"
    />
</x-layouts.dashboard>
