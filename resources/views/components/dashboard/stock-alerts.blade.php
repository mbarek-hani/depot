@use('App\Support\Format')

@props([
    'title',
    'description',
    'products',
    'icon' => 'warning',
    'tone' => 'warning',
    'emptyTitle',
    'emptyDescription',
    'link' => null,
])

@php
    $tones = [
        'warning' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300',
        'danger' => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300',
    ];
@endphp

<x-ui.card padded="false">
    <div class="flex items-center justify-between gap-3 border-b border-stone-100 px-5 py-4 dark:border-night-800">
        <div class="flex items-center gap-3">
            <span class="flex size-9 items-center justify-center rounded-xl {{ $tones[$tone] }}">
                <x-ui.icon :name="$icon" size="size-4.5" />
            </span>
            <div>
                <h2 class="font-display text-sm font-extrabold text-stone-900 dark:text-white">{{ $title }}</h2>
                <p class="mt-0.5 text-[11px] text-stone-500 dark:text-stone-400">{{ $description }}</p>
            </div>
        </div>
        @if ($link)
            <a href="{{ $link }}" class="text-[11px] font-bold text-brand-700 hover:underline dark:text-brand-300">عرض الكل</a>
        @endif
    </div>

    @forelse ($products as $product)
        <a href="{{ route('products.show', $product) }}" class="flex items-center gap-3 border-b border-stone-50 px-5 py-3 last:border-0 transition hover:bg-stone-50/70 dark:border-night-800/70 dark:hover:bg-night-800/40">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-stone-100 text-stone-500 dark:bg-night-800 dark:text-stone-400">
                <x-ui.icon name="cube" size="size-4.5" />
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-xs font-bold text-stone-900 dark:text-white">{{ $product->name }}</p>
                <p class="mt-0.5 truncate text-[11px] text-stone-500 dark:text-stone-400">{{ $product->category?->name }} · {{ $product->sku }}</p>
            </div>
            <div class="text-end">
                @if ($tone === 'danger')
                    <x-ui.badge tone="warning">{{ $product->expiry_date?->format('Y/m/d') }}</x-ui.badge>
                    <p class="mt-1 text-[10px] text-stone-400">{{ $product->expiryStatus()->label() }}</p>
                @else
                    <x-ui.badge :tone="$product->stockStatus()->tone()">{{ Format::quantity($product->quantity) }} {{ $product->unit }}</x-ui.badge>
                    <p class="mt-1 text-[10px] text-stone-400">الحد: {{ Format::quantity($product->lowStockThresholdValue()) }}</p>
                @endif
            </div>
        </a>
    @empty
        <x-ui.empty-state :icon="$icon" :title="$emptyTitle" :description="$emptyDescription" />
    @endforelse
</x-ui.card>
