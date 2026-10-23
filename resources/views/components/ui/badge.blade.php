@props(['tone' => 'neutral', 'dot' => false, 'icon' => null])

@php
    $tones = [
        'brand' => 'bg-brand-100 text-brand-900 ring-brand-200 dark:bg-brand-400/15 dark:text-brand-200 dark:ring-brand-400/25',
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20',
        'danger' => 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/20',
        'info' => 'bg-sky-50 text-sky-700 ring-sky-200 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/20',
        'neutral' => 'bg-stone-100 text-stone-600 ring-stone-200 dark:bg-white/5 dark:text-stone-300 dark:ring-white/10',
        'maroon' => 'bg-maroon-50 text-maroon-700 ring-maroon-100 dark:bg-maroon-500/10 dark:text-maroon-100 dark:ring-maroon-500/20',
    ];

    $dots = [
        'brand' => 'bg-brand-500',
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-rose-500',
        'info' => 'bg-sky-500',
        'neutral' => 'bg-stone-400',
        'maroon' => 'bg-maroon-500',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 ring-inset '.$tones[$tone]]) }}>
    @if ($dot)
        <span class="size-1.5 rounded-full {{ $dots[$tone] }}"></span>
    @endif
    @if ($icon)
        <x-ui.icon :name="$icon" size="size-3.5" />
    @endif
    {{ $slot }}
</span>
