@props([
    'label',
    'value',
    'caption',
    'icon' => 'report',
    'tone' => 'blue',
    'direction' => null,
])

@php
    $tones = [
        'blue' => ['icon' => 'bg-blue-50 text-blue-700 ring-blue-100', 'value' => 'text-blue-950', 'accent' => 'bg-blue-600'],
        'cyan' => ['icon' => 'bg-cyan-50 text-cyan-700 ring-cyan-100', 'value' => 'text-cyan-950', 'accent' => 'bg-cyan-500'],
        'violet' => ['icon' => 'bg-violet-50 text-violet-700 ring-violet-100', 'value' => 'text-violet-950', 'accent' => 'bg-violet-600'],
        'emerald' => ['icon' => 'bg-emerald-50 text-emerald-700 ring-emerald-100', 'value' => 'text-emerald-950', 'accent' => 'bg-emerald-500'],
    ];
    $classes = $tones[$tone] ?? $tones['blue'];
@endphp

<article {{ $attributes->merge(['class' => 'group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-200/60']) }}>
    <span class="absolute inset-x-0 top-0 h-0.5 {{ $classes['accent'] }}"></span>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <span class="text-sm font-bold text-slate-500">{{ $label }}</span>
            <strong class="mt-3 block truncate text-3xl font-black tracking-tight {{ $classes['value'] }}" @if ($direction) dir="{{ $direction }}" @endif>{{ $value }}</strong>
        </div>
        <span class="grid size-11 shrink-0 place-items-center rounded-xl ring-1 transition duration-300 group-hover:scale-105 {{ $classes['icon'] }}"><x-ui-icon :name="$icon" class="size-5" /></span>
    </div>
    <div class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-3 text-xs text-slate-500">
        <span class="size-1.5 rounded-full {{ $classes['accent'] }}"></span>
        <span>{{ $caption }}</span>
    </div>
</article>
