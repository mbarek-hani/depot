@use('App\Support\Format')
@use('App\Enums\StockMovementType')

@props(['movements'])

<x-ui.card padded="false" class="xl:col-span-2">
    <div class="flex items-center justify-between gap-3 border-b border-stone-100 px-5 py-4 dark:border-night-800">
        <div>
            <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">أحدث الحركات</h2>
            <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">آخر عمليات الإدخال والإخراج</p>
        </div>
        <a href="{{ route('movements.index') }}" class="text-[11px] font-bold text-brand-700 hover:underline dark:text-brand-300">عرض الكل</a>
    </div>

    @forelse ($movements as $movement)
        <div class="flex items-center gap-3 border-b border-stone-50 px-5 py-3.5 last:border-0 dark:border-night-800/70">
            <span @class([
                'flex size-9 shrink-0 items-center justify-center rounded-xl',
                'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300' => $movement->type === StockMovementType::In,
                'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300' => $movement->type === StockMovementType::Out,
            ])>
                <x-ui.icon :name="$movement->type === StockMovementType::In ? 'trending-up' : 'trending-down'" size="size-4.5" />
            </span>

            <div class="min-w-0 flex-1">
                <a href="{{ $movement->product ? route('products.show', $movement->product) : '#' }}" class="block truncate text-xs font-bold text-stone-900 hover:text-brand-700 dark:text-white dark:hover:text-brand-300">
                    {{ $movement->product?->name ?? 'منتج محذوف' }}
                </a>
                <p class="mt-0.5 truncate text-[11px] text-stone-500 dark:text-stone-400">
                    {{ $movement->user?->name ?? '—' }} · {{ $movement->reason->label() }}
                </p>
            </div>

            <div class="text-end">
                <p @class([
                    'text-sm font-extrabold tabular',
                    'text-emerald-600 dark:text-emerald-400' => $movement->type === StockMovementType::In,
                    'text-rose-600 dark:text-rose-400' => $movement->type === StockMovementType::Out,
                ])>{{ $movement->type->sign().Format::quantity($movement->quantity) }}</p>
                <p class="mt-0.5 text-[10px] font-medium text-stone-400">{{ $movement->occurred_at->diffForHumans() }}</p>
            </div>
        </div>
    @empty
        <x-ui.empty-state icon="arrows-right-left" title="لا توجد حركات بعد" description="ستظهر هنا عمليات إدخال وإخراج المخزون." />
    @endforelse
</x-ui.card>
