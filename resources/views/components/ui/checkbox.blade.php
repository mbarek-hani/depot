@props(['name', 'label', 'description' => null, 'checked' => false])

<label for="check-{{ $name }}" class="flex cursor-pointer items-start gap-3 rounded-xl border border-stone-200 bg-white p-3.5 transition hover:border-stone-300 dark:border-night-800 dark:bg-night-950 dark:hover:border-stone-700">
    <input
        type="checkbox"
        name="{{ $name }}"
        id="check-{{ $name }}"
        value="1"
        @checked((bool) old($name, $checked))
        {{ $attributes->merge(['class' => 'mt-0.5 size-4.5 rounded border-stone-300 text-brand-500 focus:ring-brand-400 dark:border-night-700 dark:bg-night-900']) }}
    >
    <span class="min-w-0">
        <span class="block text-sm font-semibold text-stone-800 dark:text-stone-100">{{ $label }}</span>
        @if ($description)
            <span class="mt-0.5 block text-xs leading-5 text-stone-500 dark:text-stone-400">{{ $description }}</span>
        @endif
    </span>
</label>
