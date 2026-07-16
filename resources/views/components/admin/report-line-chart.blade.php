@props(['series', 'chartId', 'color' => '#2563eb', 'money' => false, 'label' => 'رسم بياني'])

@php
    $plotLeft = 65; $plotRight = 900; $plotTop = 20; $plotBottom = 245;
    $plotWidth = $plotRight - $plotLeft; $plotHeight = $plotBottom - $plotTop;
    $highestValue = max(1, (float) collect($series)->max('value'));
    $tickSize = max(1, (float) ceil($highestValue / 4)); $axisMaximum = $tickSize * 4;
    $pointStep = count($series) > 1 ? $plotWidth / (count($series) - 1) : 0;
    $labelStep = max(1, (int) ceil(count($series) / 7));
    $points = collect($series)->map(function (array $item, int $index) use ($plotLeft, $pointStep, $plotBottom, $plotHeight, $axisMaximum): array {
        return [...$item, 'x' => round($plotLeft + ($index * $pointStep), 2), 'y' => round($plotBottom - (((float) $item['value'] / $axisMaximum) * $plotHeight), 2)];
    });
    $polyline = $points->map(fn (array $point): string => $point['x'].','.$point['y'])->implode(' ');
    $area = $plotLeft.','.$plotBottom.' '.$polyline.' '.$plotRight.','.$plotBottom;
@endphp

<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-2xl bg-slate-50/70 ring-1 ring-slate-100']) }}>
    <svg class="h-80 min-w-180 w-full 2xl:min-w-0" viewBox="0 0 920 290" role="img" aria-label="{{ $label }}" data-chart="{{ $chartId }}">
        <title>{{ $label }}</title>
        <defs><linearGradient id="{{ $chartId }}-area" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="{{ $color }}" stop-opacity="0.24" /><stop offset="100%" stop-color="{{ $color }}" stop-opacity="0.02" /></linearGradient></defs>
        @for ($tick = 0; $tick <= 4; $tick++)
            @php $tickValue = $tickSize * (4 - $tick); $tickY = $plotTop + (($plotHeight / 4) * $tick); @endphp
            <line x1="{{ $plotLeft }}" x2="{{ $plotRight }}" y1="{{ $tickY }}" y2="{{ $tickY }}" stroke="#e2e8f0" stroke-dasharray="4 5" />
            <text x="54" y="{{ $tickY + 4 }}" fill="#64748b" font-size="11" text-anchor="end">{{ number_format($tickValue, 0) }}</text>
        @endfor
        <line x1="{{ $plotLeft }}" x2="{{ $plotLeft }}" y1="{{ $plotTop }}" y2="{{ $plotBottom }}" stroke="#94a3b8" /><line x1="{{ $plotLeft }}" x2="{{ $plotRight }}" y1="{{ $plotBottom }}" y2="{{ $plotBottom }}" stroke="#94a3b8" />
        <polygon points="{{ $area }}" fill="url(#{{ $chartId }}-area)" /><polyline points="{{ $polyline }}" fill="none" stroke="{{ $color }}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
        @foreach ($points as $index => $point)
            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" fill="white" stroke="{{ $color }}" stroke-width="2"><title>{{ $point['label'] }}: {{ number_format($point['value'], $money ? 2 : 0) }}{{ $money ? ' ج.م' : ' حجز' }}</title></circle>
            @if ($index % $labelStep === 0 || $loop->last)<text x="{{ $point['x'] }}" y="270" fill="#64748b" font-size="11" text-anchor="middle">{{ $point['label'] }}</text>@endif
        @endforeach
        <text x="18" y="136" fill="#475569" font-size="12" font-weight="700" text-anchor="middle" transform="rotate(-90 18 136)">{{ $money ? 'الربح (ج.م)' : 'عدد الحجوزات' }}</text><text x="480" y="288" fill="#475569" font-size="12" font-weight="700" text-anchor="middle">الزمن</text>
    </svg>
</div>
