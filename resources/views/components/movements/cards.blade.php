@use('App\Support\Format')
@use('App\Enums\StockMovementType')

@props(['movements', 'showProduct' => true])

<div class="space-y-3 lg:hidden">
    @foreach ($movements as $movement)
        <article class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    @if ($showProduct)
                        @if ($movement->product && ! $movement->product->trashed())
                            <a href="{{ route('products.show', $movement->product) }}" class="block truncate text-sm font-bold text-stone-900 dark:text-white">{{ $movement->product->name }}</a>
                        @else
                            <span class="block truncate text-sm font-bold text-stone-500 dark:text-stone-400">{{ $movement->product?->name ?? 'منتج محذوف' }}</span>
                        @endif
                        <p class="mt-0.5 font-mono text-[10px] text-stone-400">
                            {{ $movement->product?->sku }}
                            @if ($movement->product?->trashed())
                                · مؤرشف
                            @endif
                        </p>
                    @else
                        <p class="text-sm font-bold text-stone-900 dark:text-white">{{ $movement->reason->label() }}</p>
                        <p class="mt-0.5 text-[10px] text-stone-400">{{ $movement->user?->name ?? '—' }}</p>
                    @endif
                </div>
                <x-ui.badge :tone="$movement->type->tone()" dot>{{ $movement->type->label() }}</x-ui.badge>
            </div>

            <div class="mt-3 flex items-center justify-between gap-3 rounded-xl bg-stone-50 px-3 py-2.5 dark:bg-night-950">
                <div>
                    <p class="text-[10px] font-semibold text-stone-400">الكمية</p>
                    <p @class([
                        'mt-0.5 text-sm font-extrabold tabular',
                        'text-emerald-600 dark:text-emerald-400' => $movement->type === StockMovementType::In,
                        'text-rose-600 dark:text-rose-400' => $movement->type === StockMovementType::Out,
                    ])>{{ $movement->type->sign().Format::quantity($movement->quantity) }} <span class="text-[10px] font-medium text-stone-400">{{ $movement->product?->unit }}</span></p>
                </div>
                <div class="text-end">
                    <p class="text-[10px] font-semibold text-stone-400">التاريخ</p>
                    <p class="mt-0.5 text-[11px] font-semibold text-stone-700 tabular dark:text-stone-200">{{ Format::dateTime($movement->occurred_at) }}</p>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between gap-3 text-[11px]">
                <span class="text-stone-500 dark:text-stone-400">
                    {{ $movement->reason->label() }}
                    @if ($showProduct && $movement->user)
                        · {{ $movement->user->name }}
                    @endif
                </span>
                @if ($movement->note)
                    <span class="max-w-[55%] truncate text-stone-400" title="{{ $movement->note }}">{{ $movement->note }}</span>
                @endif
            </div>
        </article>
    @endforeach

    <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
        <x-ui.pagination :paginator="$movements" />
    </div>
</div>
