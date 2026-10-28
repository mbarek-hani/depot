<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../Components/AppLayout.vue';
import PageHeader from '../Components/PageHeader.vue';
import StatCard from '../Components/StatCard.vue';
import ChartCanvas from '../Components/ChartCanvas.vue';
import EmptyState from '../Components/EmptyState.vue';
import Icon from '../Components/Icon.vue';
import UiButton from '../Components/UiButton.vue';

const props = defineProps({
    stats: { type: Object, required: true },
    chart: { type: Object, required: true },
    period: { type: String, default: '7d' },
    periods: { type: Array, default: () => [] },
    categoryChart: { type: Object, required: true },
    recentMovements: { type: Array, default: () => [] },
    lowStockProducts: { type: Array, default: () => [] },
    expiringProducts: { type: Array, default: () => [] },
});

const page = usePage();
const user = page.props.auth?.user;

const cards = [
    { label: 'إجمالي المنتجات', key: 'products', hint: 'منتج مسجل', href: route('products.index') },
    { label: 'إجمالي الكميات', key: 'quantity_formatted', hint: 'مجموع الكميات بالمخزون', href: route('products.index') },
    { label: 'مخزون منخفض', key: 'low_stock', hint: 'بحاجة إلى إعادة تعبئة', href: route('products.index', { status: 'low' }) },
    { label: 'نفد المخزون', key: 'out_of_stock', hint: 'منتجات غير متوفرة', href: route('products.index', { status: 'out' }) },
    { label: 'قريب الانتهاء', key: 'expiring', hint: 'خلال فترة التنبيه', href: route('products.index', { status: 'expiring' }) },
    { label: 'منتهي الصلاحية', key: 'expired', hint: 'يحتاج مراجعة', href: route('products.index', { status: 'expired' }) },
    { label: 'قيمة المخزون', key: 'stock_cost_formatted', hint: 'بحسب سعر التكلفة', href: route('reports.index', { type: 'stock' }) },
    { label: 'قيمة البيع المتوقعة', key: 'stock_sale_formatted', hint: 'بحسب سعر البيع', href: route('reports.index', { type: 'stock' }) },
    { label: 'حركات اليوم', key: 'movements_today', hint: 'إدخال وإخراج', href: route('movements.index') },
];

const currentPeriodLabel = computed(
    () => props.periods.find((item) => item.value === props.period)?.label ?? 'آخر 7 أيام',
);

function changePeriod(period) {
    if (period === props.period) {
        return;
    }

    router.get(route('dashboard'), { period }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['chart', 'period'],
    });
}
</script>

<template>
    <AppLayout title="لوحة التحكم">
        <PageHeader :title="`مرحبًا، ${user?.name}`" description="إليك نظرة سريعة على حالة المخزون والتنبيهات والحركات الأخيرة." eyebrow="نظرة عامة">
            <template #actions>
                <UiButton :href="route('reports.index')" variant="outline" icon="download">تصدير تقرير</UiButton>
                <UiButton :href="route('products.create')" icon="plus">إضافة منتج</UiButton>
            </template>
        </PageHeader>

        <div class="mt-6 space-y-4 sm:space-y-5">
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                <StatCard
                    v-for="card in cards"
                    :key="card.key"
                    :label="card.label"
                    :value="stats[card.key]"
                    :hint="card.hint"
                    :href="card.href"
                />
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                <div class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card xl:col-span-2 dark:border-night-800 dark:bg-night-900">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">حركة المخزون</h2>
                            <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">{{ currentPeriodLabel }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex flex-wrap gap-1 rounded-xl border border-stone-200 bg-stone-50 p-1 dark:border-night-800 dark:bg-night-950">
                                <button
                                    v-for="item in periods"
                                    :key="item.value"
                                    type="button"
                                    class="rounded-lg px-2.5 py-1.5 text-[10px] font-bold transition sm:text-[11px]"
                                    :class="period === item.value
                                        ? 'bg-brand-400 text-night-950 shadow-sm'
                                        : 'text-stone-500 hover:bg-white hover:text-stone-900 dark:text-stone-400 dark:hover:bg-night-800 dark:hover:text-white'"
                                    :aria-pressed="period === item.value"
                                    @click="changePeriod(item.value)"
                                >
                                    {{ item.label }}
                                </button>
                            </div>
                            <div class="flex items-center gap-4 text-[11px] font-semibold text-stone-500 dark:text-stone-400">
                                <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-brand-400"></span> إدخال</span>
                                <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-maroon-500"></span> إخراج</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5">
                        <ChartCanvas type="bar" :payload="chart" :height="270" />
                    </div>
                </div>

                <div class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card dark:border-night-800 dark:bg-night-900">
                    <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">توزيع المخزون</h2>
                    <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">حسب الأصناف</p>

                    <template v-if="categoryChart.labels?.length">
                        <ChartCanvas type="doughnut" :payload="categoryChart" :height="220" />
                        <div class="mt-3 rounded-xl bg-stone-50 p-3 text-center dark:bg-night-950">
                            <p class="text-[11px] font-semibold text-stone-500 dark:text-stone-400">إجمالي الكميات</p>
                            <p class="mt-0.5 font-display text-xl font-extrabold text-stone-900 tabular dark:text-white">{{ stats.quantity_formatted }}</p>
                        </div>
                    </template>
                    <EmptyState v-else icon="chart-bar" title="لا توجد بيانات" description="أضف منتجات وكميات لعرض توزيع المخزون." />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card xl:col-span-2 dark:border-night-800 dark:bg-night-900">
                    <div class="flex items-center justify-between gap-3 border-b border-stone-100 px-5 py-4 dark:border-night-800">
                        <div>
                            <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">أحدث الحركات</h2>
                            <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">آخر عمليات الإدخال والإخراج</p>
                        </div>
                        <Link :href="route('movements.index')" class="text-[11px] font-bold text-brand-700 hover:underline dark:text-brand-300">عرض الكل</Link>
                    </div>

                    <div v-if="recentMovements.length">
                        <div v-for="movement in recentMovements" :key="movement.id" class="flex items-center gap-3 border-b border-stone-50 px-5 py-3.5 last:border-0 dark:border-night-800/70">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl" :class="movement.is_in ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300'">
                                <Icon :name="movement.is_in ? 'trending-up' : 'trending-down'" :size="18" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <Link v-if="movement.product_url" :href="movement.product_url" class="block truncate text-xs font-bold text-stone-900 hover:text-brand-700 dark:text-white dark:hover:text-brand-300">{{ movement.product_name }}</Link>
                                <p v-else class="truncate text-xs font-bold text-stone-500 dark:text-stone-400">{{ movement.product_name }}</p>
                                <p class="mt-0.5 truncate text-[11px] text-stone-500 dark:text-stone-400">{{ movement.user_name ?? '—' }} · {{ movement.reason_label }}</p>
                            </div>
                            <div class="text-end">
                                <p class="text-sm font-extrabold tabular" :class="movement.is_in ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">{{ movement.sign }}{{ movement.quantity_formatted }}</p>
                                <p class="mt-0.5 text-[10px] font-medium text-stone-400">{{ movement.occurred_human }}</p>
                            </div>
                        </div>
                    </div>
                    <EmptyState v-else icon="arrows-right-left" title="لا توجد حركات بعد" description="ستظهر هنا عمليات إدخال وإخراج المخزون." />
                </div>

                <div class="space-y-4">
                    <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
                        <div class="flex items-center justify-between gap-3 border-b border-stone-100 px-5 py-4 dark:border-night-800">
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300">
                                    <Icon name="warning" :size="18" />
                                </span>
                                <div>
                                    <h2 class="font-display text-sm font-extrabold text-stone-900 dark:text-white">مخزون منخفض</h2>
                                    <p class="mt-0.5 text-[11px] text-stone-500 dark:text-stone-400">منتجات وصلت إلى الحد الأدنى</p>
                                </div>
                            </div>
                            <Link :href="route('products.index', { status: 'low' })" class="text-[11px] font-bold text-brand-700 hover:underline dark:text-brand-300">الكل</Link>
                        </div>

                        <div v-if="lowStockProducts.length">
                            <Link v-for="product in lowStockProducts" :key="product.id" :href="route('products.show', product.id)" class="flex items-center gap-3 border-b border-stone-50 px-5 py-3 last:border-0 hover:bg-stone-50/70 dark:border-night-800/70 dark:hover:bg-night-800/40">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-bold text-stone-900 dark:text-white">{{ product.name }}</p>
                                    <p class="mt-0.5 truncate text-[11px] text-stone-500 dark:text-stone-400">{{ product.category_name }} · {{ product.sku }}</p>
                                </div>
                                <div class="text-end">
                                    <p class="text-xs font-extrabold text-amber-700 tabular dark:text-amber-300">{{ product.quantity_formatted }} {{ product.unit }}</p>
                                    <p class="mt-0.5 text-[10px] text-stone-400">الحد: {{ product.threshold_formatted }}</p>
                                </div>
                            </Link>
                        </div>
                        <EmptyState v-else icon="warning" title="المخزون بحالة جيدة" description="لا توجد منتجات منخفضة الكمية حاليًا." />
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
                        <div class="flex items-center justify-between gap-3 border-b border-stone-100 px-5 py-4 dark:border-night-800">
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300">
                                    <Icon name="calendar" :size="18" />
                                </span>
                                <div>
                                    <h2 class="font-display text-sm font-extrabold text-stone-900 dark:text-white">قريب الانتهاء</h2>
                                    <p class="mt-0.5 text-[11px] text-stone-500 dark:text-stone-400">راقب تواريخ الصلاحية</p>
                                </div>
                            </div>
                            <Link :href="route('products.index', { status: 'expiring' })" class="text-[11px] font-bold text-brand-700 hover:underline dark:text-brand-300">الكل</Link>
                        </div>

                        <div v-if="expiringProducts.length">
                            <Link v-for="product in expiringProducts" :key="product.id" :href="route('products.show', product.id)" class="flex items-center gap-3 border-b border-stone-50 px-5 py-3 last:border-0 hover:bg-stone-50/70 dark:border-night-800/70 dark:hover:bg-night-800/40">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-bold text-stone-900 dark:text-white">{{ product.name }}</p>
                                    <p class="mt-0.5 truncate text-[11px] text-stone-500 dark:text-stone-400">{{ product.category_name }} · {{ product.sku }}</p>
                                </div>
                                <div class="text-end">
                                    <p class="text-xs font-extrabold text-rose-700 tabular dark:text-rose-300">{{ product.expiry_date_formatted }}</p>
                                    <p class="mt-0.5 text-[10px] text-stone-400">{{ product.expiry_label }}</p>
                                </div>
                            </Link>
                        </div>
                        <EmptyState v-else icon="calendar" title="لا توجد منتجات قريبة الانتهاء" description="كل المنتجات ذات الصلاحية سليمة حاليًا." />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
