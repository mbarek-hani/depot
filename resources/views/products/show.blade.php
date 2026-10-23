@use('App\Support\Format')

<x-layouts.app :title="$product->name">
    <x-layout.page-header
        :title="$product->name"
        :description="'الرمز '.$product->sku.' · '.$product->category?->name"
        eyebrow="تفاصيل المنتج"
    >
        <x-slot:actions>
            <x-ui.button :href="route('products.index')" variant="outline" icon="arrow-right">المنتجات</x-ui.button>

            @if (auth()->user()->isAdmin())
                <x-ui.button type="button" variant="brand-soft" icon="arrows-right-left" x-on:click="$dispatch('open-modal', 'stock-movement-modal')">تسجيل حركة</x-ui.button>
                <x-ui.button :href="route('products.edit', $product)" icon="pencil">تعديل</x-ui.button>
                <x-ui.button type="button" variant="danger-soft" size="icon" icon="trash" x-on:click="$dispatch('open-modal', 'delete-product-modal')" aria-label="حذف المنتج" />
            @endif
        </x-slot:actions>
    </x-layout.page-header>

    <div class="mt-6 space-y-4">
        <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            <x-ui.stat-card label="الكمية الحالية" :value="Format::quantity($product->quantity).' '.$product->unit" icon="archive" tone="brand" hint="الكمية المتوفرة بالمخزون" />
            <x-ui.stat-card label="حالة المخزون" :value="$product->stockStatus()->label()" icon="shield" :tone="$product->stockStatus()->tone()" :hint="'حد التنبيه: '.Format::quantity($product->lowStockThresholdValue())" />
            <x-ui.stat-card label="قيمة المخزون" :value="Format::money($product->stockCostValue())" icon="banknotes" tone="success" hint="بحسب سعر التكلفة" />
            <x-ui.stat-card label="الصلاحية" :value="$product->expiry_date?->format('Y/m/d') ?? 'غير محددة'" icon="calendar" :tone="$product->expiryStatus()->tone()" :hint="$product->expiryStatus()->label()" />
        </div>

        <div class="grid gap-4 xl:grid-cols-3">
            <x-ui.card class="xl:col-span-2">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">بيانات المنتج</h2>
                        <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">الأسعار والتصنيف والملاحظات.</p>
                    </div>
                    <x-products.status-badges :product="$product" />
                </div>

                <dl class="mt-5 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-[11px] font-semibold text-stone-400">الصنف</dt>
                        <dd class="mt-1 flex items-center gap-2 text-sm font-bold text-stone-800 dark:text-stone-100">
                            <span class="size-2.5 rounded-full {{ $product->category?->colorClasses() }}"></span>
                            {{ $product->category?->name ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold text-stone-400">وحدة القياس</dt>
                        <dd class="mt-1 text-sm font-bold text-stone-800 dark:text-stone-100">{{ $product->unit }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold text-stone-400">سعر التكلفة</dt>
                        <dd class="mt-1 text-sm font-bold text-stone-800 tabular dark:text-stone-100">{{ Format::money($product->cost_price) }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold text-stone-400">سعر البيع</dt>
                        <dd class="mt-1 text-sm font-bold text-stone-800 tabular dark:text-stone-100">{{ Format::money($product->sale_price) }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold text-stone-400">قيمة البيع المتوقعة</dt>
                        <dd class="mt-1 text-sm font-bold text-emerald-700 tabular dark:text-emerald-400">{{ Format::money($product->stockSaleValue()) }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold text-stone-400">هامش الربح المتوقع</dt>
                        <dd class="mt-1 text-sm font-bold text-stone-800 tabular dark:text-stone-100">{{ Format::money($product->stockSaleValue() - $product->stockCostValue()) }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-[11px] font-semibold text-stone-400">ملاحظات</dt>
                        <dd class="mt-1 text-sm leading-6 text-stone-600 dark:text-stone-300">{{ $product->notes ?: 'لا توجد ملاحظات.' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card>
                <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">ملخص الحركات</h2>
                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between rounded-xl bg-emerald-50 px-4 py-3 dark:bg-emerald-500/10">
                        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">إجمالي الإدخال</span>
                        <span class="font-display text-lg font-extrabold text-emerald-700 tabular dark:text-emerald-300">+{{ Format::quantity($totals['in']) }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-rose-50 px-4 py-3 dark:bg-rose-500/10">
                        <span class="text-xs font-semibold text-rose-700 dark:text-rose-300">إجمالي الإخراج</span>
                        <span class="font-display text-lg font-extrabold text-rose-700 tabular dark:text-rose-300">-{{ Format::quantity($totals['out']) }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-stone-100 px-4 py-3 dark:bg-night-800">
                        <span class="text-xs font-semibold text-stone-600 dark:text-stone-300">الرصيد الحالي</span>
                        <span class="font-display text-lg font-extrabold text-stone-900 tabular dark:text-white">{{ Format::quantity($product->quantity) }}</span>
                    </div>
                </div>

                @if ($product->expiry_date)
                    <div class="mt-4 rounded-xl border border-stone-200 p-4 dark:border-night-800">
                        <p class="text-[11px] font-semibold text-stone-400">تاريخ الانتهاء</p>
                        <p class="mt-1 text-sm font-bold text-stone-900 dark:text-white">{{ Format::longDate($product->expiry_date) }}</p>
                        <div class="mt-2">
                            <x-ui.badge :tone="$product->expiryStatus()->tone()">{{ $product->expiryStatus()->label() }}</x-ui.badge>
                        </div>
                    </div>
                @endif
            </x-ui.card>
        </div>

        <div>
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <h2 class="font-display text-lg font-extrabold text-stone-900 dark:text-white">سجل الحركات</h2>
                    <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">كل عمليات الإدخال والإخراج لهذا المنتج.</p>
                </div>
                @if (auth()->user()->isAdmin())
                    <x-ui.button type="button" size="sm" variant="outline" icon="plus" x-on:click="$dispatch('open-modal', 'stock-movement-modal')">حركة جديدة</x-ui.button>
                @endif
            </div>

            @if ($movements->isEmpty())
                <div class="rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
                    <x-ui.empty-state icon="arrows-right-left" title="لا توجد حركات" description="ستظهر هنا حركات المخزون الخاصة بهذا المنتج." />
                </div>
            @else
                <x-movements.table :movements="$movements" :show-product="false" />
                <x-movements.cards :movements="$movements" :show-product="false" />
            @endif
        </div>
    </div>

    @if (auth()->user()->isAdmin())
        <x-ui.modal name="stock-movement-modal" title="تسجيل حركة مخزون" description="أضف أو اسحب كمية من مخزون هذا المنتج.">
            <x-movements.form :fixed-product="$product" />
        </x-ui.modal>

        <x-ui.modal name="delete-product-modal" title="تأكيد حذف المنتج" size="sm">
            <div class="flex items-start gap-3 rounded-xl bg-rose-50 p-4 text-sm text-rose-800 dark:bg-rose-500/10 dark:text-rose-200">
                <x-ui.icon name="warning" size="size-5" class="mt-0.5 shrink-0" />
                <p>سيتم أرشفة «{{ $product->name }}». ستبقى الحركات محفوظة في السجل.</p>
            </div>
            <x-slot:footer>
                <x-ui.button type="button" variant="outline" x-on:click="$dispatch('close-modal', 'delete-product-modal')">إلغاء</x-ui.button>
                <form method="POST" action="{{ route('products.destroy', $product) }}">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="danger" icon="trash">حذف المنتج</x-ui.button>
                </form>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</x-layouts.app>
