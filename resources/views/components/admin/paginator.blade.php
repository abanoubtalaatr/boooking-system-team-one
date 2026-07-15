@props([
    'paginator' => null,
    'itemLabel' => 'نتيجة',
    'perPageOptions' => null,
    'currentPerPage' => 15,
    'perPageLabel' => 'عدد العناصر في الصفحة',
    'useWire' => false,
])

@php
    use Illuminate\Contracts\Pagination\LengthAwarePaginator;
    use Illuminate\Pagination\UrlWindow;

    $perPageBtnBase = 'inline-flex items-center justify-center min-w-[42px] px-[11px] py-[7px] border rounded-full text-xs font-bold no-underline transition-colors cursor-pointer';
    $perPageBtnDefault = 'border-[var(--line)] bg-[var(--surface)] text-[var(--ink)] hover:border-[rgb(20_93_184/35%)] hover:bg-[var(--brand-soft)] hover:text-[var(--brand-dark)]';
    $perPageBtnActive = 'border-[var(--brand)] bg-[var(--brand)] text-white hover:bg-[var(--brand)] hover:text-white';

    $pageBtnBase = 'inline-flex items-center justify-center min-w-[38px] h-[38px] px-2.5 border rounded-[10px] text-[0.82rem] font-bold no-underline transition-colors cursor-pointer';
    $pageBtnDefault = 'border-[var(--line)] bg-[var(--surface)] text-[var(--ink)] hover:border-[rgb(20_93_184/35%)] hover:bg-[var(--brand-soft)] hover:text-[var(--brand-dark)]';
    $pageBtnActive = 'border-[var(--brand)] bg-[var(--brand)] text-white';
    $pageBtnDisabled = 'border-[var(--line)] bg-[var(--surface)] text-[var(--ink)] opacity-45 cursor-not-allowed';
    $pageBtnLabel = 'min-w-[72px] px-3.5 text-xs';

    $elements = [];

    if ($paginator instanceof LengthAwarePaginator && $paginator->hasPages()) {
        $window = UrlWindow::make($paginator);

        $elements = array_filter([
            $window['first'],
            is_array($window['slider']) ? '...' : null,
            $window['slider'],
            is_array($window['last']) ? '...' : null,
            $window['last'],
        ]);
    }
@endphp

@if ($perPageOptions)
    <div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2']) }} role="group" aria-label="{{ $perPageLabel }}">
        <span class="text-xs text-[var(--muted)]">{{ $perPageLabel }}:</span>
        @foreach ($perPageOptions as $option)
            @if ($useWire)
                <button
                    type="button"
                    wire:click="setPerPage({{ $option }})"
                    wire:loading.attr="disabled"
                    @class([$perPageBtnBase, (int) $currentPerPage === $option ? $perPageBtnActive : $perPageBtnDefault])
                    aria-current="{{ (int) $currentPerPage === $option ? 'true' : 'false' }}"
                >
                    {{ $option }}
                </button>
            @else
                <a
                    href="{{ request()->fullUrlWithQuery(['per_page' => $option, 'page' => 1]) }}"
                    @class([$perPageBtnBase, (int) $currentPerPage === $option ? $perPageBtnActive : $perPageBtnDefault])
                    aria-current="{{ (int) $currentPerPage === $option ? 'true' : 'false' }}"
                >
                    {{ $option }}
                </a>
            @endif
        @endforeach
    </div>
@endif

@if ($paginator instanceof LengthAwarePaginator)
    <div {{ $attributes->merge(['class' => 'mt-[18px] flex flex-col gap-3.5']) }}>
        <p class="m-0 text-[0.82rem] text-[var(--muted)]" wire:loading.remove wire:target="search,setPerPage,gotoPage,previousPage,nextPage">
            @if ($paginator->total() > 0)
                عرض {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} من {{ $paginator->total() }} {{ $itemLabel }}
                · الصفحة {{ $paginator->currentPage() }} من {{ $paginator->lastPage() }}
            @else
                لا توجد {{ $itemLabel }} للعرض
            @endif
        </p>

        @if ($useWire)
            <p class="m-0 text-[0.82rem] text-[var(--muted)]" wire:loading wire:target="search,setPerPage,gotoPage,previousPage,nextPage">
                جاري التحديث...
            </p>
        @endif

        @if ($paginator->hasPages())
            <nav class="flex flex-wrap items-center justify-center gap-2" role="navigation" aria-label="التنقل بين الصفحات">
                @if ($paginator->onFirstPage())
                    <span @class([$pageBtnBase, $pageBtnLabel, $pageBtnDisabled]) aria-disabled="true" aria-label="الصفحة السابقة">السابق</span>
                @elseif ($useWire)
                    <button type="button" wire:click="previousPage" wire:loading.attr="disabled" @class([$pageBtnBase, $pageBtnLabel, $pageBtnDefault]) aria-label="الصفحة السابقة">السابق</button>
                @else
                    <a @class([$pageBtnBase, $pageBtnLabel, $pageBtnDefault]) href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="الصفحة السابقة">السابق</a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex min-w-7 items-center justify-center text-[0.82rem] text-[var(--muted)]" aria-hidden="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span @class([$pageBtnBase, $pageBtnActive]) aria-current="page">{{ $page }}</span>
                            @elseif ($useWire)
                                <button type="button" wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled" @class([$pageBtnBase, $pageBtnDefault]) aria-label="الانتقال إلى الصفحة {{ $page }}">{{ $page }}</button>
                            @else
                                <a @class([$pageBtnBase, $pageBtnDefault]) href="{{ $url }}" aria-label="الانتقال إلى الصفحة {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    @if ($useWire)
                        <button type="button" wire:click="nextPage" wire:loading.attr="disabled" @class([$pageBtnBase, $pageBtnLabel, $pageBtnDefault]) aria-label="الصفحة التالية">التالي</button>
                    @else
                        <a @class([$pageBtnBase, $pageBtnLabel, $pageBtnDefault]) href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="الصفحة التالية">التالي</a>
                    @endif
                @else
                    <span @class([$pageBtnBase, $pageBtnLabel, $pageBtnDisabled]) aria-disabled="true" aria-label="الصفحة التالية">التالي</span>
                @endif
            </nav>
        @endif
    </div>
@endif
