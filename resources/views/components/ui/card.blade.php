@props(['padded' => true])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900']) }}>
    <div class="{{ $padded ? 'p-5 sm:p-6' : '' }}">
        {{ $slot }}
    </div>
</div>
