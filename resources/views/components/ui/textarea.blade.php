@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'rows' => 4,
])

@php
    $id = $attributes->get('id') ?? 'field-'.str_replace(['[', ']', '.'], ['-', '', '-'], $name);
    $hasError = $errors->has($name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-stone-700 dark:text-stone-200">{{ $label }}</label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $id }}"
        rows="{{ $rows }}"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        {{ $attributes->except('class')->merge([
            'class' => 'w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-stone-900 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:bg-night-950 dark:text-white dark:placeholder:text-stone-500 '.($hasError ? 'border-rose-400 dark:border-rose-500/60' : 'border-stone-300 dark:border-night-800'),
        ]) }}
    >{{ old($name, $value) }}</textarea>

    @if ($hint)
        <p class="text-xs text-stone-400 dark:text-stone-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
    @enderror
</div>
