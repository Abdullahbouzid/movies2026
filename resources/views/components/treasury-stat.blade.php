@props([
    'label',
    'value',
    'caption' => null,
    'icon' => 'heroicon-o-banknotes',
    'tone' => 'primary',
    'progress' => null,
])

@php
    $tones = [
        'success' => ['text' => 'text-emerald-600 dark:text-emerald-400', 'chip' => 'bg-emerald-50 text-emerald-600 ring-emerald-600/10 dark:bg-emerald-400/10 dark:text-emerald-400', 'bar' => 'bg-emerald-500'],
        'warning' => ['text' => 'text-amber-600 dark:text-amber-400', 'chip' => 'bg-amber-50 text-amber-600 ring-amber-600/10 dark:bg-amber-400/10 dark:text-amber-400', 'bar' => 'bg-amber-500'],
        'primary' => ['text' => 'text-primary-600 dark:text-primary-400', 'chip' => 'bg-primary-50 text-primary-600 ring-primary-600/10 dark:bg-primary-400/10 dark:text-primary-400', 'bar' => 'bg-primary-500'],
        'gray' => ['text' => 'text-gray-900 dark:text-white', 'chip' => 'bg-gray-100 text-gray-600 ring-gray-600/10 dark:bg-white/5 dark:text-gray-400', 'bar' => 'bg-gray-400'],
    ];
    $t = $tones[$tone] ?? $tones['primary'];
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-white/10 dark:bg-gray-900']) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg ring-1 {{ $t['chip'] }}">
            <x-filament::icon :icon="$icon" class="h-5 w-5" />
        </span>
    </div>

    <p class="mt-3 text-2xl font-bold tracking-tight {{ $t['text'] }}">{{ $value }}</p>

    @if ($caption)
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $caption }}</p>
    @endif

    @if (! is_null($progress))
        <div class="mt-4 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
            <div class="h-full rounded-full {{ $t['bar'] }}" style="width: {{ max(0, min(100, (float) $progress)) }}%"></div>
        </div>
    @endif
</div>
