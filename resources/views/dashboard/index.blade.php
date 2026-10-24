@use('App\Support\Format')

<x-layouts.app title="لوحة التحكم">
    <x-layout.page-header
        :title="'مرحبًا، '.auth()->user()->name"
        description="إليك نظرة سريعة على حالة المخزون والتنبيهات والحركات الأخيرة."
        eyebrow="نظرة عامة"
    >
        <x-slot:actions>
            <x-ui.button :href="route('reports.index')" variant="outline" icon="download">تصدير تقرير</x-ui.button>
            @if (auth()->user()->isAdmin())
                <x-ui.button :href="route('products.create')" icon="plus">إضافة منتج</x-ui.button>
            @endif
        </x-slot:actions>
    </x-layout.page-header>

    <div class="mt-6 space-y-4 sm:space-y-5">
        <x-dashboard.stats :stats="$stats" />

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <x-dashboard.movement-chart :chart="$chart" />
            <x-dashboard.category-chart :chart="$categoryChart" :total="$stats['quantity']" />
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <x-dashboard.recent-movements :movements="$recentMovements" />

            <div class="space-y-4">
                <x-dashboard.stock-alerts
                    title="مخزون منخفض"
                    description="منتجات وصلت إلى الحد الأدنى"
                    :products="$lowStockProducts"
                    icon="warning"
                    tone="warning"
                    empty-title="المخزون بحالة جيدة"
                    empty-description="لا توجد منتجات منخفضة الكمية حاليًا."
                    :link="route('products.index', ['status' => 'low'])"
                />

                <x-dashboard.stock-alerts
                    title="قريب الانتهاء"
                    description="راقب تواريخ الصلاحية"
                    :products="$expiringProducts"
                    icon="calendar"
                    tone="danger"
                    empty-title="لا توجد منتجات قريبة الانتهاء"
                    empty-description="كل المنتجات ذات الصلاحية سليمة حاليًا."
                    :link="route('products.index', ['status' => 'expiring'])"
                />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
                <p class="text-[11px] font-semibold text-stone-500 dark:text-stone-400">حركات اليوم</p>
                <p class="mt-1 font-display text-xl font-extrabold text-stone-900 tabular dark:text-white">{{ number_format($stats['movements_today']) }}</p>
            </div>
            <div class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
                <p class="text-[11px] font-semibold text-stone-500 dark:text-stone-400">دخل اليوم</p>
                <p class="mt-1 font-display text-xl font-extrabold text-emerald-600 tabular dark:text-emerald-400">+{{ Format::quantity($stats['in_today']) }}</p>
            </div>
            <div class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
                <p class="text-[11px] font-semibold text-stone-500 dark:text-stone-400">خرج اليوم</p>
                <p class="mt-1 font-display text-xl font-extrabold text-rose-600 tabular dark:text-rose-400">-{{ Format::quantity($stats['out_today']) }}</p>
            </div>
        </div>
    </div>
</x-layouts.app>
