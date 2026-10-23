@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'hint' => null,
    'required' => false,
])

@php
    $id = $attributes->get('id') ?? 'field-'.str_replace(['[', ']', '.'], ['-', '', '-'], $name);
    $hasError = $errors->has($name);
    $current = old($name, $selected);
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

    <select
        name="{{ $name }}"
        id="{{ $id }}"
        @if ($required) required @endif
        {{ $attributes->except('class')->merge([
            'class' => 'h-11 w-full rounded-xl border bg-white px-3.5 text-sm text-stone-900 shadow-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:bg-night-950 dark:text-white '.($hasError ? 'border-rose-400 dark:border-rose-500/60' : 'border-stone-300 dark:border-night-800'),
        ]) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @if ($slot->isNotEmpty())
            {{ $slot }}
        @else
            @foreach ($options as $value => $labelText)
                <option value="{{ $value }}" @selected((string) $current === (string) $value)>{{ $labelText }}</option>
            @endforeach
        @endif
    </select>

    @if ($hint)
        <p class="text-xs text-stone-400 dark:text-stone-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
    @enderror
</div>
