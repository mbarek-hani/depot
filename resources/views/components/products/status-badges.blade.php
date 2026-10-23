@props(['product'])

@php
    $stock = $product->stockStatus();
    $expiry = $product->expiryStatus();
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-1.5']) }}>
    <x-ui.badge :tone="$stock->tone()" dot>{{ $stock->label() }}</x-ui.badge>

    @if ($expiry->value !== 'none')
        <x-ui.badge :tone="$expiry->tone()">{{ $expiry->label() }}</x-ui.badge>
    @endif
</div>
