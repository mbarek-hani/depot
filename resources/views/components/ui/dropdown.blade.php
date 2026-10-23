@props([
    'align' => 'end',
    'width' => 'w-64',
    'panelClass' => '',
])

<div x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape.window="open = false" {{ $attributes->merge(['class' => 'relative']) }}>
    <div x-on:click="open = !open" :aria-expanded="open.toString()">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute z-50 mt-2 {{ $width }} {{ $align === 'start' ? 'start-0' : 'end-0' }} overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-pop dark:border-night-800 dark:bg-night-900 {{ $panelClass }}"
    >
        {{ $slot }}
    </div>
</div>
