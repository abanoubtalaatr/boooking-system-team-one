<x-layouts.dashboard title="المفضلة لدى المرضى" role="admin">
    <div class="breadcrumb">الرئيسية / المفضلة لدى المرضى</div>
    <div class="page-head">
        <div>
            <h1 class="page-title">المفضلة لدى المرضى</h1>
            <p class="page-description">عدد الأطباء المفضلين لكل مريض. اضغط على المريض لعرض قائمة أطبائه المفضلين.</p>
        </div>
    </div>

    <article class="panel">
        <div class="panel-head">
            <h2 class="panel-title">المرضى</h2>
            <form method="GET" action="{{ route('admin.patient-favorites') }}" class="search" role="search">
                <span><x-ui-icon name="search" /></span>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="ابحث باسم المريض..." aria-label="بحث عن مريض">
            </form>
        </div>

        @forelse ($patients as $patient)
            <a class="schedule-item" href="{{ route('admin.patient-favorites.show', $patient) }}">
                <span class="avatar">{{ mb_substr($patient->name ?? 'م', 0, 1) }}</span>
                <span class="schedule-copy">
                    <strong>{{ $patient->name ?? 'مريض محذوف' }}</strong>
                    <small>{{ $patient->email }}</small>
                </span>
                <span class="status">{{ $patient->favorites_count }} طبيب مفضل</span>
            </a>
        @empty
            <p class="field-hint">لا يوجد مرضى لعرضهم.</p>
        @endforelse
    </article>

    <div class="pagination-wrap">
        {{ $patients->links() }}
    </div>
</x-layouts.dashboard>
