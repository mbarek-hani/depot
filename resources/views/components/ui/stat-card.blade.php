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

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'group flex min-w-0 items-start justify-between gap-3 rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card transition hover:-translate-y-0.5 hover:border-stone-300 dark:border-night-800 dark:bg-night-900 dark:hover:border-stone-700 sm:gap-4 sm:p-5']) }}>
    <div class="min-w-0">
        <p class="text-[11px] font-semibold leading-5 text-stone-500 dark:text-stone-400 sm:text-xs">{{ $label }}</p>
        <p class="mt-1.5 font-display text-lg font-extrabold leading-tight tracking-tight text-stone-900 tabular dark:text-white sm:mt-2 sm:text-2xl xl:text-3xl">{{ $value }}</p>
        @if ($hint)
            <p class="mt-1 text-[10px] font-medium leading-4 text-stone-400 dark:text-stone-500 sm:mt-1.5 sm:text-[11px] sm:leading-5">{{ $hint }}</p>
        @endif
    </div>
    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] }} sm:size-11">
        <x-ui.icon :name="$icon" size="size-4 sm:size-5" />
    </span>
</{{ $tag }}>
