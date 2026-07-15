<div>
    <x-admin.paginator
        :per-page-options="$perPageOptions"
        :current-per-page="$perPage"
        per-page-label="عدد المرضى في الصفحة"
        use-wire
        class="mb-3.5"
    />

    <article class="panel" wire:loading.class="opacity-60" wire:target="search,setPerPage,gotoPage,previousPage,nextPage">
        <div class="panel-head">
            <h2 class="panel-title">المرضى</h2>
            <label class="search" role="search">
                <span><x-ui-icon name="search" /></span>
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="ابحث باسم المريض..."
                    aria-label="بحث عن مريض"
                >
            </label>
        </div>

        @forelse ($patients as $patient)
            <a class="schedule-item" href="{{ route('admin.search-history.show', $patient) }}" wire:key="patient-{{ $patient->id }}">
                <span class="avatar">{{ mb_substr($patient->name ?? 'م', 0, 1) }}</span>
                <span class="schedule-copy">
                    <strong>{{ $patient->name ?? 'مريض محذوف' }}</strong>
                    <small>{{ $patient->email }}</small>
                </span>
                <span class="status">{{ $patient->search_histories_count }} عملية بحث</span>
            </a>
        @empty
            <p class="field-hint">لا يوجد مرضى لعرضهم.</p>
        @endforelse
    </article>

    <x-admin.paginator
        :paginator="$patients"
        item-label="مريض"
        use-wire
    />
</div>
