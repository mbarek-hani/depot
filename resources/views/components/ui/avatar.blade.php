@props(['name', 'size' => 'size-9'])

@php
    $initials = collect(preg_split('/\s+/u', trim($name)))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
@endphp

<span {{ $attributes->merge(['class' => $size.' inline-flex shrink-0 items-center justify-center rounded-full bg-brand-400 font-display text-xs font-extrabold text-night-950']) }}>
    {{ $initials ?: 'م' }}
</span>
