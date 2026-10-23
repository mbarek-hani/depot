@props(['name', 'label', 'description' => null, 'checked' => false])

<label for="toggle-{{ $name }}" class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-stone-200 bg-white p-4 transition hover:border-stone-300 dark:border-night-800 dark:bg-night-950 dark:hover:border-stone-700">
    <span class="min-w-0">
        <span class="block text-sm font-semibold text-stone-800 dark:text-stone-100">{{ $label }}</span>
        @if ($description)
            <span class="mt-0.5 block text-xs leading-5 text-stone-500 dark:text-stone-400">{{ $description }}</span>
        @endif
    </span>
    <span class="relative inline-flex shrink-0">
        <input type="hidden" name="{{ $name }}" value="0">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="toggle-{{ $name }}"
            value="1"
            @checked((bool) old($name, $checked))
            {{ $attributes->merge(['class' => 'peer sr-only']) }}
        >
        <span class="h-6 w-11 rounded-full bg-stone-300 transition peer-checked:bg-brand-400 peer-focus-visible:ring-4 peer-focus-visible:ring-brand-400/30 dark:bg-night-700"></span>
        <span class="pointer-events-none absolute start-0.5 top-0.5 size-5 rounded-full bg-white shadow transition peer-checked:translate-x-[-1.25rem]"></span>
    </span>
</label>
