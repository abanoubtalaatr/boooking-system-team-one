@props(['series'])

@php
    $plotLeft = 65; $plotRight = 900; $plotTop = 20; $plotBottom = 245;
    $plotWidth = $plotRight - $plotLeft; $plotHeight = $plotBottom - $plotTop;
    $highestValue = max(1, (int) collect($series)->flatMap(fn (array $item): array => [$item['card'], $item['cash']])->max());
    $tickSize = max(1, (int) ceil($highestValue / 4)); $axisMaximum = $tickSize * 4;
    $groupWidth = $plotWidth / max(1, count($series)); $barWidth = min(18, max(4, ($groupWidth - 6) / 2));
    $labelStep = max(1, (int) ceil(count($series) / 7));
@endphp

<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-2xl bg-slate-50/70 ring-1 ring-slate-100']) }}>
    <svg class="h-80 min-w-180 w-full 2xl:min-w-0" viewBox="0 0 920 290" role="img" aria-label="مقارنة حجوزات الفيزا والكاش" data-chart="payment-methods">
        <title>مقارنة حجوزات الفيزا والكاش</title>
        @for ($tick = 0; $tick <= 4; $tick++)
            @php $tickValue = $tickSize * (4 - $tick); $tickY = $plotTop + (($plotHeight / 4) * $tick); @endphp
            <line x1="{{ $plotLeft }}" x2="{{ $plotRight }}" y1="{{ $tickY }}" y2="{{ $tickY }}" stroke="#e2e8f0" stroke-dasharray="4 5" /><text x="54" y="{{ $tickY + 4 }}" fill="#64748b" font-size="11" text-anchor="end">{{ $tickValue }}</text>
        @endfor
        <line x1="{{ $plotLeft }}" x2="{{ $plotLeft }}" y1="{{ $plotTop }}" y2="{{ $plotBottom }}" stroke="#94a3b8" /><line x1="{{ $plotLeft }}" x2="{{ $plotRight }}" y1="{{ $plotBottom }}" y2="{{ $plotBottom }}" stroke="#94a3b8" />
        @foreach ($series as $index => $item)
            @php $center = $plotLeft + ($groupWidth * $index) + ($groupWidth / 2); $cardHeight = ($item['card'] / $axisMaximum) * $plotHeight; $cashHeight = ($item['cash'] / $axisMaximum) * $plotHeight; @endphp
            <rect x="{{ $center - $barWidth - 1 }}" y="{{ $plotBottom - $cardHeight }}" width="{{ $barWidth }}" height="{{ $cardHeight }}" rx="3" fill="#7c3aed"><title>{{ $item['label'] }}: {{ $item['card'] }} فيزا</title></rect>
            <rect x="{{ $center + 1 }}" y="{{ $plotBottom - $cashHeight }}" width="{{ $barWidth }}" height="{{ $cashHeight }}" rx="3" fill="#10b981"><title>{{ $item['label'] }}: {{ $item['cash'] }} كاش</title></rect>
            @if ($index % $labelStep === 0 || $loop->last)<text x="{{ $center }}" y="270" fill="#64748b" font-size="11" text-anchor="middle">{{ $item['label'] }}</text>@endif
        @endforeach
        <text x="18" y="136" fill="#475569" font-size="12" font-weight="700" text-anchor="middle" transform="rotate(-90 18 136)">عدد الحجوزات</text><text x="480" y="288" fill="#475569" font-size="12" font-weight="700" text-anchor="middle">الزمن</text>
    </svg>
</div>
