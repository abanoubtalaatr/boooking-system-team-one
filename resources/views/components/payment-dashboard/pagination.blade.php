@props(['paginator'])

@if ($paginator->hasPages())
    <nav class="dashboard-pagination" aria-label="التنقل بين صفحات المدفوعات">
        <span>صفحة {{ $paginator->currentPage() }} من {{ $paginator->lastPage() }} — {{ $paginator->total() }} عملية</span>
        <div>
            @if ($paginator->onFirstPage())
                <span class="pagination-link is-disabled">السابق</span>
            @else
                <a class="pagination-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">السابق</a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="pagination-link" href="{{ $paginator->nextPageUrl() }}" rel="next">التالي</a>
            @else
                <span class="pagination-link is-disabled">التالي</span>
            @endif
        </div>
    </nav>
@endif
