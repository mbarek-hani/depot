@props(['size' => 'md', 'withText' => false, 'textClass' => 'text-white'])

@php
    $sizes = [
        'sm' => 'size-9',
        'md' => 'size-11',
        'lg' => 'size-14',
        'xl' => 'size-20',
    ];

    $logo = collect(['images/logo.png', 'images/logo.svg', 'images/logo.jpg', 'images/logo.jpeg'])
        ->first(fn (string $path) => file_exists(public_path($path)));
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    @if ($logo)
        <img src="{{ asset($logo) }}" alt="{{ $associationName ?? config('app.name') }}" class="{{ $sizes[$size] }} shrink-0 rounded-xl bg-white object-contain p-0.5 shadow-sm">
    @else
        <span class="{{ $sizes[$size] }} flex shrink-0 items-center justify-center rounded-xl bg-brand-400 text-night-950 shadow-sm">
            <x-ui.icon name="store" :size="$size === 'sm' ? 'size-4.5' : ($size === 'xl' ? 'size-10' : 'size-6')" />
        </span>
    @endif

    @if ($withText)
        <span class="min-w-0">
            <span class="block truncate font-display text-sm font-extrabold {{ $textClass }}">{{ $associationName ?? config('app.name') }}</span>
            <span class="block text-[10px] font-medium text-stone-400">إدارة المخزون</span>
        </span>
    @endif
</div>
