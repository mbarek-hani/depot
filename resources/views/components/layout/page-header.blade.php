@props(['title', 'description' => null, 'eyebrow' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        @if ($eyebrow)
            <p class="mb-1 text-[11px] font-bold uppercase tracking-widest text-brand-700 dark:text-brand-300">{{ $eyebrow }}</p>
        @endif
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-stone-900 dark:text-white sm:text-3xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1.5 max-w-2xl text-sm leading-6 text-stone-500 dark:text-stone-400">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2 sm:shrink-0">
            {{ $actions }}
        </div>
    @endisset
</div>
