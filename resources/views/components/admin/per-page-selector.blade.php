@props([
    'perPageOptions' => [15, 30, 50, 100],
    'currentPerPage' => 15,
    'label' => 'عدد العناصر في الصفحة',
])

@php
    $perPageBtnBase = 'inline-flex items-center justify-center min-w-[42px] px-[11px] py-[7px] border rounded-full text-xs font-bold no-underline transition-colors';
    $perPageBtnDefault = 'border-[var(--line)] bg-[var(--surface)] text-[var(--ink)] hover:border-[rgb(20_93_184/35%)] hover:bg-[var(--brand-soft)] hover:text-[var(--brand-dark)]';
    $perPageBtnActive = 'border-[var(--brand)] bg-[var(--brand)] text-white hover:bg-[var(--brand)] hover:text-white';
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2']) }} role="group" aria-label="{{ $label }}">
    <span class="text-xs text-[var(--muted)]">{{ $label }}:</span>
    @foreach ($perPageOptions as $option)
        <a
            href="{{ request()->fullUrlWithQuery(['per_page' => $option, 'page' => 1]) }}"
            @class([$perPageBtnBase, (int) $currentPerPage === $option ? $perPageBtnActive : $perPageBtnDefault])
            aria-current="{{ (int) $currentPerPage === $option ? 'true' : 'false' }}"
        >
            {{ $option }}
        </a>
    @endforeach
</div>
