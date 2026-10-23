@props([
    'tone' => 'info',
    'icon' => null,
    'dismissible' => true,
])

@php
    $tones = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200',
        'danger' => 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-200',
        'info' => 'border-sky-200 bg-sky-50 text-sky-800 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-200',
    ];

    $icons = [
        'success' => 'check-circle',
        'warning' => 'warning',
        'danger' => 'warning',
        'info' => 'info',
    ];

    $icon ??= $icons[$tone];
@endphp

<div
    @if ($dismissible) x-data="{ show: true }" x-show="show" x-transition @endif
    {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xl border px-4 py-3 text-sm '.$tones[$tone]]) }}
    role="status"
>
    <x-ui.icon :name="$icon" size="size-5" class="mt-0.5 shrink-0" />
    <div class="min-w-0 flex-1 leading-6">{{ $slot }}</div>
    @if ($dismissible)
        <button type="button" x-on:click="show = false" class="shrink-0 rounded-md p-0.5 opacity-60 transition hover:opacity-100" aria-label="إغلاق التنبيه">
            <x-ui.icon name="x" size="size-4" />
        </button>
    @endif
</div>
