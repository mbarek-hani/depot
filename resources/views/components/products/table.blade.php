@use('App\Support\Format')

@props(['products'])

<div class="hidden overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900 lg:block">
    <div class="overflow-x-auto scrollbar-thin">
        <table class="w-full min-w-[56rem] text-start">
            <thead>
                <tr class="border-b border-stone-100 bg-stone-50/70 text-[11px] font-bold text-stone-500 dark:border-night-800 dark:bg-night-950/60 dark:text-stone-400">
                    <th class="px-5 py-3.5 text-start font-bold">المنتج</th>
                    <th class="px-4 py-3.5 text-start font-bold">الصنف</th>
                    <th class="px-4 py-3.5 text-start font-bold">الكمية</th>
                    <th class="px-4 py-3.5 text-start font-bold">الحالة</th>
                    <th class="px-4 py-3.5 text-start font-bold">الصلاحية</th>
                    <th class="px-4 py-3.5 text-start font-bold">قيمة التكلفة</th>
                    <th class="px-5 py-3.5 text-end font-bold">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-night-800">
                @foreach ($products as $product)
                    <tr class="transition hover:bg-stone-50/60 dark:hover:bg-night-800/30">
                        <td class="px-5 py-3.5">
                            <a href="{{ route('products.show', $product) }}" class="group flex items-center gap-3">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-400/10 dark:text-brand-300">
                                    <x-ui.icon name="cube" size="size-4.5" />
                                </span>
                                <span class="min-w-0">
                                    <span class="block max-w-56 truncate text-xs font-bold text-stone-900 group-hover:text-brand-700 dark:text-white dark:group-hover:text-brand-300">{{ $product->name }}</span>
                                    <span class="mt-0.5 block font-mono text-[10px] text-stone-400">{{ $product->sku }}</span>
                                </span>
                            </a>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center gap-1.5 text-xs text-stone-600 dark:text-stone-300">
                                <span class="size-2 rounded-full {{ $product->category?->colorClasses() }}"></span>
                                {{ $product->category?->name ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            <p class="text-xs font-bold text-stone-900 tabular dark:text-white">{{ Format::quantity($product->quantity) }} <span class="text-[10px] font-medium text-stone-400">{{ $product->unit }}</span></p>
                            <p class="mt-0.5 text-[10px] text-stone-400">الحد: {{ Format::quantity($product->lowStockThresholdValue()) }}</p>
                        </td>
                        <td class="px-4 py-3.5">
                            <x-products.status-badges :product="$product" />
                        </td>
                        <td class="px-4 py-3.5">
                            @if ($product->expiry_date)
                                <p class="text-xs font-semibold text-stone-700 dark:text-stone-200">{{ Format::date($product->expiry_date) }}</p>
                                <p class="mt-0.5 text-[10px] text-stone-400">{{ $product->expiryStatus()->label() }}</p>
                            @else
                                <span class="text-xs text-stone-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <p class="text-xs font-bold text-stone-900 tabular dark:text-white">{{ Format::money($product->stockCostValue()) }}</p>
                            <p class="mt-0.5 text-[10px] text-stone-400">بيع: {{ Format::money($product->stockSaleValue()) }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                <x-ui.button :href="route('products.show', $product)" variant="ghost" size="icon" icon="eye" aria-label="عرض المنتج" />

                                @if (auth()->user()->isAdmin())
                                    <x-ui.button :href="route('products.edit', $product)" variant="ghost" size="icon" icon="pencil" aria-label="تعديل المنتج" />
                                    <button type="button" x-on:click="$dispatch('open-modal', 'delete-product-{{ $product->id }}')" class="inline-flex size-10 items-center justify-center rounded-xl text-rose-500 transition hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-500/10" aria-label="حذف المنتج">
                                        <x-ui.icon name="trash" size="size-4.5" />
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <x-ui.pagination :paginator="$products" />
</div>
