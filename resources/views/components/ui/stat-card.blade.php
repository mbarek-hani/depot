@props([
    'label',
    'value',
    'icon' => 'cube',
    'tone' => 'brand',
    'hint' => null,
    'href' => null,
])

@php
    $tones = [
        'brand' => 'bg-brand-100 text-brand-900 dark:bg-brand-400/15 dark:text-brand-300',
        'success' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300',
        'warning' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300',
        'danger' => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300',
        'info' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300',
        'neutral' => 'bg-stone-100 text-stone-600 dark:bg-white/5 dark:text-stone-300',
    ];
@endphp

@php $tag = $href ? 'a' : 'div'; @endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'group flex items-start justify-between gap-4 rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card transition hover:-translate-y-0.5 hover:border-stone-300 dark:border-night-800 dark:bg-night-900 dark:hover:border-stone-700']) }}>
    <div class="min-w-0">
        <p class="text-xs font-semibold text-stone-500 dark:text-stone-400">{{ $label }}</p>
        <p class="mt-2 truncate font-display text-2xl font-extrabold tracking-tight text-stone-900 tabular dark:text-white sm:text-3xl">{{ $value }}</p>
        @if ($hint)
            <p class="mt-1.5 text-[11px] font-medium text-stone-400 dark:text-stone-500">{{ $hint }}</p>
        @endif
    </div>
    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] }}">
        <x-ui.icon :name="$icon" size="size-5" />
    </span>
</{{ $tag }}>
