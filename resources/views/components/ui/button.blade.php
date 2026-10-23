@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'href' => null,
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'bg-brand-400 text-night-950 shadow-sm hover:bg-brand-300 focus-visible:outline-brand-500 dark:bg-brand-400 dark:hover:bg-brand-300',
        'secondary' => 'bg-stone-900 text-white shadow-sm hover:bg-stone-800 dark:bg-white dark:text-night-950 dark:hover:bg-stone-200',
        'outline' => 'border border-stone-300 bg-white text-stone-700 hover:border-stone-400 hover:bg-stone-50 dark:border-night-800 dark:bg-night-900 dark:text-stone-200 dark:hover:border-stone-700 dark:hover:bg-night-800',
        'ghost' => 'text-stone-600 hover:bg-stone-100 hover:text-stone-900 dark:text-stone-300 dark:hover:bg-night-800 dark:hover:text-white',
        'danger' => 'bg-rose-600 text-white shadow-sm hover:bg-rose-700',
        'danger-soft' => 'bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:hover:bg-rose-500/20',
        'brand-soft' => 'bg-brand-100 text-brand-900 hover:bg-brand-200 dark:bg-brand-400/15 dark:text-brand-200 dark:hover:bg-brand-400/25',
    ];

    $sizes = [
        'sm' => 'h-9 gap-1.5 px-3 text-xs',
        'md' => 'h-10 gap-2 px-4 text-sm',
        'lg' => 'h-12 gap-2.5 px-5 text-sm',
        'icon' => 'size-10 justify-center p-0',
    ];

    $iconSizes = [
        'sm' => 'size-4',
        'md' => 'size-4.5',
        'lg' => 'size-5',
        'icon' => 'size-5',
    ];

    $classes = 'inline-flex shrink-0 items-center justify-center rounded-xl font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 disabled:pointer-events-none disabled:opacity-50 '.$variants[$variant].' '.$sizes[$size];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-ui.icon :name="$icon" :size="$iconSizes[$size]" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-ui.icon :name="$icon" :size="$iconSizes[$size]" />
        @endif
        {{ $slot }}
    </button>
@endif
