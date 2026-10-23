@props(['icon' => 'inbox', 'title', 'description' => null])

<div class="flex flex-col items-center justify-center px-6 py-14 text-center">
    <span class="flex size-14 items-center justify-center rounded-2xl bg-brand-100 text-brand-900 dark:bg-brand-400/15 dark:text-brand-300">
        <x-ui.icon :name="$icon" size="size-6" />
    </span>
    <h3 class="mt-4 font-display text-base font-extrabold text-stone-900 dark:text-white">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1.5 max-w-sm text-sm leading-6 text-stone-500 dark:text-stone-400">{{ $description }}</p>
    @endif
    @isset($actions)
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2">{{ $actions }}</div>
    @endisset
</div>
