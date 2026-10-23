@use('App\Support\Format')

<x-layouts.app title="الدخول والخروج">
    <x-layout.page-header
        title="حركات المخزون"
        description="سجل كامل لعمليات الإدخال والإخراج مع المستخدم والسبب."
        eyebrow="المخزون"
    >
        <x-slot:actions>
            @if (auth()->user()->isAdmin() && $products->isNotEmpty())
                <x-ui.button type="button" icon="plus" x-on:click="$dispatch('open-modal', 'stock-movement-modal')">تسجيل حركة</x-ui.button>
            @endif
        </x-slot:actions>
    </x-layout.page-header>

    <div class="mt-6 space-y-4">
        <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            <x-ui.stat-card label="عدد الحركات" :value="number_format($totals['count'])" icon="arrows-right-left" tone="brand" />
            <x-ui.stat-card label="إجمالي الإدخال" :value="Format::quantity($totals['in'])" icon="trending-up" tone="success" />
            <x-ui.stat-card label="إجمالي الإخراج" :value="Format::quantity($totals['out'])" icon="trending-down" tone="danger" />
            <x-ui.stat-card label="صافي الحركة" :value="Format::quantity($totals['in'] - $totals['out'])" icon="chart-bar" tone="info" />
        </div>

        <x-movements.filters :filters="$filters" :categories="$categories" />

        @if ($movements->isEmpty())
            <div class="rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
                <x-ui.empty-state icon="arrows-right-left" title="لا توجد حركات مطابقة" description="جرّب تعديل الفلاتر أو سجّل حركة مخزون جديدة.">
                    <x-slot:actions>
                        @if (auth()->user()->isAdmin() && $products->isNotEmpty())
                            <x-ui.button type="button" icon="plus" x-on:click="$dispatch('open-modal', 'stock-movement-modal')">تسجيل حركة</x-ui.button>
                        @endif
                    </x-slot:actions>
                </x-ui.empty-state>
            </div>
        @else
            <x-movements.table :movements="$movements" />
            <x-movements.cards :movements="$movements" />
        @endif
    </div>

    @if (auth()->user()->isAdmin())
        <x-ui.modal name="stock-movement-modal" title="تسجيل حركة مخزون" description="اختر المنتج ونوع الحركة والكمية." size="lg">
            <x-movements.form :products="$products" />
        </x-ui.modal>
    @endif
</x-layouts.app>
