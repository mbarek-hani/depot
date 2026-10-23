@props([
    'name',
    'title' => null,
    'description' => null,
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
    ];
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[80] flex items-end justify-center p-0 sm:items-center sm:p-6"
    role="dialog"
    aria-modal="true"
    @if ($title) aria-labelledby="modal-title-{{ $name }}" @endif
>
    <div
        x-show="open"
        x-transition.opacity.duration.200ms
        class="absolute inset-0 bg-night-950/60 backdrop-blur-sm"
        x-on:click="open = false"
        aria-hidden="true"
    ></div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100"
        x-transition:leave-end="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
        class="relative w-full {{ $sizes[$size] }} overflow-hidden rounded-t-2xl border border-stone-200 bg-white shadow-pop dark:border-night-800 dark:bg-night-900 sm:rounded-2xl"
    >
        <div class="flex items-start justify-between gap-4 border-b border-stone-100 px-5 py-4 dark:border-night-800 sm:px-6">
            <div>
                @if ($title)
                    <h2 id="modal-title-{{ $name }}" class="font-display text-lg font-extrabold text-stone-900 dark:text-white">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="mt-1 text-xs leading-5 text-stone-500 dark:text-stone-400">{{ $description }}</p>
                @endif
            </div>
            <button type="button" x-on:click="open = false" class="rounded-lg p-1.5 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700 dark:hover:bg-night-800 dark:hover:text-stone-200" aria-label="إغلاق">
                <x-ui.icon name="x" size="size-5" />
            </button>
        </div>

        <div class="max-h-[70vh] overflow-y-auto px-5 py-5 scrollbar-thin sm:px-6">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="flex flex-col-reverse gap-2 border-t border-stone-100 bg-stone-50/70 px-5 py-4 dark:border-night-800 dark:bg-night-950/40 sm:flex-row sm:justify-end sm:px-6">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
