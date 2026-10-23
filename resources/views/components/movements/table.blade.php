@use('App\Support\Format')
@use('App\Enums\StockMovementType')

@props(['movements', 'showProduct' => true])

<div class="hidden overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900 lg:block">
    <div class="overflow-x-auto scrollbar-thin">
        <table class="w-full min-w-[52rem] text-start">
            <thead>
                <tr class="border-b border-stone-100 bg-stone-50/70 text-[11px] font-bold text-stone-500 dark:border-night-800 dark:bg-night-950/60 dark:text-stone-400">
                    <th class="px-5 py-3.5 text-start font-bold">التاريخ</th>
                    @if ($showProduct)
                        <th class="px-4 py-3.5 text-start font-bold">المنتج</th>
                    @endif
                    <th class="px-4 py-3.5 text-start font-bold">النوع</th>
                    <th class="px-4 py-3.5 text-start font-bold">الكمية</th>
                    <th class="px-4 py-3.5 text-start font-bold">السبب</th>
                    <th class="px-4 py-3.5 text-start font-bold">المستخدم</th>
                    <th class="px-5 py-3.5 text-start font-bold">ملاحظات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-night-800">
                @foreach ($movements as $movement)
                    <tr class="transition hover:bg-stone-50/60 dark:hover:bg-night-800/30">
                        <td class="whitespace-nowrap px-5 py-3.5">
                            <p class="text-xs font-semibold text-stone-700 tabular dark:text-stone-200">{{ Format::dateTime($movement->occurred_at) }}</p>
                        </td>
                        @if ($showProduct)
                            <td class="px-4 py-3.5">
                                @if ($movement->product && ! $movement->product->trashed())
                                    <a href="{{ route('products.show', $movement->product) }}" class="block max-w-56 truncate text-xs font-bold text-stone-900 hover:text-brand-700 dark:text-white dark:hover:text-brand-300">
                                        {{ $movement->product->name }}
                                    </a>
                                @else
                                    <span class="block max-w-56 truncate text-xs font-bold text-stone-500 dark:text-stone-400">
                                        {{ $movement->product?->name ?? 'منتج محذوف' }}
                                    </span>
                                @endif
                                <p class="mt-0.5 font-mono text-[10px] text-stone-400">
                                    {{ $movement->product?->sku }}
                                    @if ($movement->product?->trashed())
                                        · مؤرشف
                                    @endif
                                </p>
                            </td>
                        @endif
                        <td class="px-4 py-3.5">
                            <x-ui.badge :tone="$movement->type->tone()" dot>{{ $movement->type->label() }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3.5">
                            <span @class([
                                'text-xs font-extrabold tabular',
                                'text-emerald-600 dark:text-emerald-400' => $movement->type === StockMovementType::In,
                                'text-rose-600 dark:text-rose-400' => $movement->type === StockMovementType::Out,
                            ])>{{ $movement->type->sign().Format::quantity($movement->quantity) }}</span>
                            <span class="text-[10px] text-stone-400">{{ $movement->product?->unit }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="text-xs font-semibold text-stone-700 dark:text-stone-200">{{ $movement->reason->label() }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="text-xs text-stone-600 dark:text-stone-300">{{ $movement->user?->name ?? '—' }}</span>
                        </td>
                        <td class="max-w-52 px-5 py-3.5">
                            <p class="truncate text-[11px] text-stone-500 dark:text-stone-400" title="{{ $movement->note }}">{{ $movement->note ?: '—' }}</p>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <x-ui.pagination :paginator="$movements" />
</div>
