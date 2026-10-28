<script setup>
import { computed, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Components/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import StatCard from '../../Components/StatCard.vue';
import UiButton from '../../Components/UiButton.vue';
import Badge from '../../Components/Badge.vue';
import EmptyState from '../../Components/EmptyState.vue';

const props = defineProps({
    type: { type: String, required: true },
    report: { type: Object, required: true },
    types: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, required: true },
});

const filters = reactive({
    category_id: props.filters.category_id ?? '',
    status: props.filters.status ?? 'all',
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
});

const inputClass = 'h-11 w-full rounded-xl border border-stone-300 bg-white px-3.5 text-xs text-stone-900 shadow-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-950 dark:text-white';

const statusOptions = computed(() => {
    if (props.report.type === 'stock') {
        return [
            { value: 'all', label: 'كل الحالات' },
            { value: 'available', label: 'متوفر' },
            { value: 'low', label: 'منخفض' },
            { value: 'out', label: 'نفد' },
        ];
    }

    if (props.report.type === 'movements') {
        return [
            { value: 'all', label: 'كل الحركات' },
            { value: 'in', label: 'إدخال' },
            { value: 'out', label: 'إخراج' },
        ];
    }

    if (props.report.type === 'expiry') {
        return [
            { value: 'all', label: 'جميع المنتجات ضمن المدة' },
            { value: 'expiring', label: 'قريبة الانتهاء' },
            { value: 'expired', label: 'منتهية' },
        ];
    }

    return [];
});

const showDates = computed(() => props.report.type === 'movements');
const downloadUrl = computed(() => route('reports.download', { ...filters, type: props.report.type }));

function apply() {
    router.get(route('reports.index'), { type: props.report.type, ...filters }, { preserveState: true, replace: true });
}

function clear() {
    router.get(route('reports.index', { type: props.report.type }));
}
</script>

<template>
    <AppLayout title="التقارير">
        <PageHeader title="التقارير" description="تقارير المخزون والحركات والصلاحية جاهزة للعرض والتنزيل بصيغة PDF." eyebrow="التحليلات">
            <template #actions>
                <UiButton :href="downloadUrl" icon="download">تنزيل PDF</UiButton>
            </template>
        </PageHeader>

        <div class="mt-6 space-y-4">
            <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
                <Link
                    v-for="item in types"
                    :key="item.value"
                    :href="route('reports.index', { ...filters, type: item.value })"
                    class="whitespace-nowrap rounded-xl px-4 py-2.5 text-xs font-bold transition"
                    :class="report.type === item.value ? 'bg-brand-400 text-night-950 shadow-sm' : 'border border-stone-200 bg-white text-stone-600 hover:border-stone-300 dark:border-night-800 dark:bg-night-900 dark:text-stone-300 dark:hover:border-stone-700'"
                >
                    {{ item.label }}
                </Link>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
                <StatCard v-for="item in report.summary" :key="item.label" :label="item.label" :value="item.value" :tone="item.tone" />
            </div>

            <div class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
                <form class="grid gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end" @submit.prevent="apply">
                    <select v-model="filters.category_id" :class="inputClass" class="lg:col-span-4">
                        <option value="">جميع الأصناف</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                    <select v-if="statusOptions.length" v-model="filters.status" :class="[inputClass, showDates ? 'lg:col-span-2' : 'lg:col-span-4']">
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                    <input v-if="showDates" v-model="filters.from" type="date" :class="inputClass" class="lg:col-span-2" aria-label="من تاريخ" dir="ltr">
                    <input v-if="showDates" v-model="filters.to" type="date" :class="inputClass" class="lg:col-span-2" aria-label="إلى تاريخ" dir="ltr">
                    <div class="flex items-center gap-2 lg:col-span-3">
                        <UiButton type="submit" icon="filter" class="flex-1">تحديث التقرير</UiButton>
                        <UiButton variant="ghost" @click="clear">مسح</UiButton>
                    </div>
                </form>
            </div>

            <div class="flex flex-col gap-2 rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card sm:flex-row sm:items-center sm:justify-between sm:p-6 dark:border-night-800 dark:bg-night-900">
                <div>
                    <h2 class="font-display text-lg font-extrabold text-stone-900 dark:text-white">{{ report.title }}</h2>
                    <p class="mt-1 text-xs leading-6 text-stone-500 dark:text-stone-400">{{ report.description }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Badge tone="neutral">{{ report.period }}</Badge>
                    <Badge tone="brand">{{ report.rows.length }} سجل</Badge>
                </div>
            </div>

            <EmptyState v-if="!report.rows.length" icon="document" title="لا توجد بيانات في هذا التقرير" description="جرّب تغيير الفلاتر أو الفترة الزمنية." class="rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900" />

            <template v-else>
                <div class="hidden overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card lg:block dark:border-night-800 dark:bg-night-900">
                    <div class="overflow-x-auto scrollbar-thin">
                        <table class="w-full min-w-[52rem]">
                            <thead>
                                <tr class="border-b border-stone-100 bg-stone-50/70 text-[11px] font-bold text-stone-500 dark:border-night-800 dark:bg-night-950/60 dark:text-stone-400">
                                    <th v-for="column in report.columns" :key="column" class="px-4 py-3.5 text-start font-bold first:ps-5 last:pe-5">{{ column }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 dark:divide-night-800">
                                <tr v-for="(row, rowIndex) in report.rows" :key="rowIndex" class="transition hover:bg-stone-50/60 dark:hover:bg-night-800/30">
                                    <td v-for="(cell, cellIndex) in row" :key="cellIndex" class="px-4 py-3 text-xs first:ps-5 last:pe-5" :class="cellIndex === 0 ? 'font-bold text-stone-900 dark:text-white' : 'text-stone-600 dark:text-stone-300'">{{ cell }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-stone-200 bg-stone-50/80 dark:border-night-800 dark:bg-night-950/60">
                                    <td :colspan="report.columns.length" class="px-5 py-3.5">
                                        <div class="flex flex-wrap items-center gap-x-8 gap-y-2">
                                            <div v-for="(value, label) in report.totals" :key="label" class="flex items-center gap-2">
                                                <span class="text-[10px] font-semibold text-stone-400">{{ label }}</span>
                                                <span class="text-xs font-extrabold text-stone-900 tabular dark:text-white">{{ value }}</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="space-y-3 lg:hidden">
                    <article v-for="(row, rowIndex) in report.rows" :key="rowIndex" class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
                        <h3 class="text-sm font-bold text-stone-900 dark:text-white">{{ row[0] ?? '—' }}</h3>
                        <dl class="mt-3 space-y-2">
                            <div v-for="(label, index) in report.columns.slice(1)" :key="label" class="flex items-start justify-between gap-3">
                                <dt class="text-[10px] font-semibold text-stone-400">{{ label }}</dt>
                                <dd class="text-end text-[11px] font-semibold text-stone-700 dark:text-stone-200">{{ row[index + 1] ?? '—' }}</dd>
                            </div>
                        </dl>
                    </article>

                    <div class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
                        <div class="flex flex-wrap gap-x-6 gap-y-2">
                            <div v-for="(value, label) in report.totals" :key="label">
                                <p class="text-[10px] font-semibold text-stone-400">{{ label }}</p>
                                <p class="mt-0.5 text-xs font-extrabold text-stone-900 tabular dark:text-white">{{ value }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
