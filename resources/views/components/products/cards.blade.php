@use('App\Support\Format')

@props(['products'])

<div class="space-y-3 lg:hidden">
    @forelse ($products as $product)
        <article class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
            <div class="flex items-start justify-between gap-3">
                <a href="{{ route('products.show', $product) }}" class="flex min-w-0 items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-400/10 dark:text-brand-300">
                        <x-ui.icon name="cube" size="size-5" />
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-bold text-stone-900 dark:text-white">{{ $product->name }}</span>
                        <span class="mt-0.5 block font-mono text-[10px] text-stone-400">{{ $product->sku }}</span>
                    </span>
                </a>
                <x-products.status-badges :product="$product" class="justify-end" />
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 rounded-xl bg-stone-50 p-3 dark:bg-night-950">
                <div>
                    <p class="text-[10px] font-semibold text-stone-400">الكمية</p>
                    <p class="mt-0.5 text-xs font-extrabold text-stone-900 tabular dark:text-white">{{ Format::quantity($product->quantity) }} {{ $product->unit }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-stone-400">الصنف</p>
                    <p class="mt-0.5 truncate text-xs font-bold text-stone-700 dark:text-stone-200">{{ $product->category?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-stone-400">الصلاحية</p>
                    <p class="mt-0.5 text-xs font-bold text-stone-700 dark:text-stone-200">{{ $product->expiry_date ? Format::date($product->expiry_date) : '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-stone-400">قيمة التكلفة</p>
                    <p class="mt-0.5 text-xs font-bold text-stone-900 tabular dark:text-white">{{ Format::money($product->stockCostValue()) }}</p>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-end gap-2">
                <x-ui.button :href="route('products.show', $product)" variant="outline" size="sm" icon="eye">عرض</x-ui.button>

                @if (auth()->user()->isAdmin())
                    <x-ui.button :href="route('products.edit', $product)" variant="brand-soft" size="sm" icon="pencil">تعديل</x-ui.button>
                    <button type="button" x-on:click="$dispatch('open-modal', 'delete-product-{{ $product->id }}')" class="inline-flex size-9 items-center justify-center rounded-xl text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-500/10" aria-label="حذف المنتج">
                        <x-ui.icon name="trash" size="size-4" />
                    </button>
                @endif
            </div>
        </article>
    @empty
        <div class="rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
            <x-ui.empty-state icon="cube" title="لا توجد منتجات" description="ابدأ بإضافة أول منتج إلى المستودع.">
                <x-slot:actions>
                    @if (auth()->user()->isAdmin())
                        <x-ui.button :href="route('products.create')" icon="plus">إضافة منتج</x-ui.button>
                    @endif
                </x-slot:actions>
            </x-ui.empty-state>
        </div>
    @endforelse

    <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
        <x-ui.pagination :paginator="$products" />
    </div>
</div>
