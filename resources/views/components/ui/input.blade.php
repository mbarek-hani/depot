@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'icon' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $id = $attributes->get('id') ?? 'field-'.str_replace(['[', ']', '.'], ['-', '', '-'], $name);
    $hasError = $errors->has($name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-stone-700 dark:text-stone-200">
            {{ $label }}
            @if ($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if ($icon)
            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-stone-400">
                <x-ui.icon :name="$icon" size="size-4.5" />
            </span>
        @endif

        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $id }}"
            value="{{ old($name, $value) }}"
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($required) required @endif
            @if ($disabled) disabled @endif
            {{ $attributes->except('class')->merge([
                'class' => 'h-11 w-full rounded-xl border bg-white px-3.5 text-sm text-stone-900 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 disabled:cursor-not-allowed disabled:bg-stone-50 dark:bg-night-950 dark:text-white dark:placeholder:text-stone-500 dark:disabled:bg-night-800 '.($icon ? 'ps-10 ' : '').($hasError ? 'border-rose-400 focus:border-rose-400 focus:ring-rose-400/20 dark:border-rose-500/60' : 'border-stone-300 dark:border-night-800'),
            ]) }}
        >
    </div>

    @if ($hint)
        <p class="text-xs text-stone-400 dark:text-stone-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
    @enderror
</div>
