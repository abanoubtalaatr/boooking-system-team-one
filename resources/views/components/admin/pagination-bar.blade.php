@props([
    'paginator',
    'itemLabel' => 'نتيجة',
])

<div {{ $attributes->merge(['class' => 'mt-[18px] flex flex-col gap-3.5']) }}>
    <p class="m-0 text-[0.82rem] text-[var(--muted)]">
        @if ($paginator->total() > 0)
            عرض {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} من {{ $paginator->total() }} {{ $itemLabel }}
            · الصفحة {{ $paginator->currentPage() }} من {{ $paginator->lastPage() }}
        @else
            لا توجد {{ $itemLabel }} للعرض
        @endif
    </p>

    {{ $paginator->links('vendor.pagination.admin') }}
</div>
