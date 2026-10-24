@props(['size' => 'md', 'withText' => false, 'textClass' => 'text-white'])

@php
    $sizes = [
        'sm' => 'size-9',
        'md' => 'size-11',
        'lg' => 'size-14',
        'xl' => 'size-20',
    ];

    $logo = 'images/logo.jpeg';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <img src="{{ asset($logo) }}" alt="{{ $associationName ?? config('app.name') }}" class="{{ $sizes[$size] }} shrink-0 rounded-xl bg-white object-contain p-0.5 shadow-sm">

    @if ($withText)
        <span class="min-w-0">
            <span class="block truncate font-display text-sm font-extrabold {{ $textClass }}">{{ $associationName ?? config('app.name') }}</span>
            <span class="block text-[10px] font-medium text-stone-400">إدارة المخزون</span>
        </span>
    @endif
</div>
