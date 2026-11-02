<script setup>
import { computed, reactive, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Components/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import StatCard from '../../Components/StatCard.vue';
import UiButton from '../../Components/UiButton.vue';
import Badge from '../../Components/Badge.vue';
import Icon from '../../Components/Icon.vue';
import Pagination from '../../Components/Pagination.vue';
import EmptyState from '../../Components/EmptyState.vue';
import Modal from '../../Components/Modal.vue';
import MovementForm from '../../Components/MovementForm.vue';

const props = defineProps({
    movements: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, required: true },
    totals: { type: Object, required: true },
    products: { type: Array, default: () => [] },
});

const filters = reactive({
    search: props.filters.search ?? '',
    type: props.filters.type ?? '',
    category_id: props.filters.category_id ?? '',
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
    sort: props.filters.sort ?? 'occurred_at|desc',
});

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.is_admin === true);

const movementOpen = ref(false);
const inputClass = 'h-10 w-full rounded-xl border border-stone-300 bg-white px-3.5 text-xs text-stone-900 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-950 dark:text-white';

function apply() {
    router.get(route('movements.index'), { ...filters }, { preserveState: true, replace: true });
}

function clear() {
    router.get(route('movements.index'));
}
</script>

<template>
    <AppLayout title="حركات المخزون">
        <PageHeader title="حركات المخزون" description="سجل كامل لعمليات الإدخال والإخراج مع المستخدم والسبب." eyebrow="المخزون">
            <template #actions>
                <UiButton v-if="isAdmin && products.length" icon="plus" @click="movementOpen = true">تسجيل حركة</UiButton>
            </template>
        </PageHeader>

        <div class="mt-6 space-y-4">
            <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
                <StatCard label="عدد الحركات" :value="totals.count" />
                <StatCard label="إجمالي الإدخال" :value="totals.in_formatted" />
                <StatCard label="إجمالي الإخراج" :value="totals.out_formatted" />
                <StatCard label="صافي الحركة" :value="totals.net_formatted" />
            </div>

            <div class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
                <form class="grid gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end" @submit.prevent="apply">
                    <div class="relative lg:col-span-4">
                        <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-stone-400"><Icon name="search" :size="16" /></span>
                        <input v-model="filters.search" :class="inputClass" class="ps-9" placeholder="ابحث عن منتج أو سبب..." />
                    </div>
                    <select v-model="filters.type" :class="inputClass" class="lg:col-span-2">
                        <option value="">كل الأنواع</option>
                        <option value="in">إدخال</option>
                        <option value="out">إخراج</option>
                    </select>
                    <select v-model="filters.category_id" :class="inputClass" class="lg:col-span-6">
                        <option value="">جميع الأصناف</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                    <input v-model="filters.from" type="date" :class="inputClass" class="lg:col-span-3" aria-label="من تاريخ" dir="ltr">
                    <input v-model="filters.to" type="date" :class="inputClass" class="lg:col-span-3" aria-label="إلى تاريخ" dir="ltr">
                    <select v-model="filters.sort" :class="inputClass" class="lg:col-span-3" aria-label="ترتيب الحركات">
                        <option value="occurred_at|desc">الأحدث</option>
                        <option value="occurred_at|asc">الأقدم</option>
                        <option value="product_name|asc">اسم المنتج</option>
                        <option value="quantity|desc">الكمية الأكبر</option>
                        <option value="quantity|asc">الكمية الأقل</option>
                    </select>
                    <div class="flex items-center gap-2 sm:col-span-2 lg:col-span-3">
                        <UiButton type="submit" icon="filter" class="flex-1">تصفية</UiButton>
                        <UiButton variant="ghost" @click="clear">مسح</UiButton>
                    </div>
                </form>

            </div>

            <EmptyState v-if="!movements.data.length" icon="arrows-right-left" title="لا توجد حركات مطابقة" description="جرّب تعديل الفلاتر أو سجّل حركة مخزون جديدة." class="rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
                <template #actions>
                    <UiButton v-if="isAdmin && products.length" icon="plus" @click="movementOpen = true">تسجيل حركة</UiButton>
                </template>
            </EmptyState>

            <template v-else>
                <div class="hidden overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card lg:block dark:border-night-800 dark:bg-night-900">
                    <div class="overflow-x-auto scrollbar-thin">
                        <table class="w-full min-w-[52rem] text-start">
                            <thead>
                                <tr class="border-b border-stone-100 bg-stone-50/70 text-[11px] font-bold text-stone-500 dark:border-night-800 dark:bg-night-950/60 dark:text-stone-400">
                                    <th class="px-5 py-3.5 text-start">التاريخ</th>
                                    <th class="px-4 py-3.5 text-start">المنتج</th>
                                    <th class="px-4 py-3.5 text-start">النوع</th>
                                    <th class="px-4 py-3.5 text-start">الكمية</th>
                                    <th class="px-4 py-3.5 text-start">السبب</th>
                                    <th class="px-4 py-3.5 text-start">المستخدم</th>
                                    <th class="px-5 py-3.5 text-start">ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 dark:divide-night-800">
                                <tr v-for="movement in movements.data" :key="movement.id" class="hover:bg-stone-50/60 dark:hover:bg-night-800/30">
                                    <td class="px-5 py-3.5 text-xs font-semibold text-stone-700 dark:text-stone-200">{{ movement.occurred_formatted }}</td>
                                    <td class="px-4 py-3.5">
                                        <Link v-if="movement.product_url" :href="movement.product_url" class="block max-w-48 truncate text-xs font-bold text-stone-900 hover:text-brand-700 dark:text-white dark:hover:text-brand-300">{{ movement.product_name }}</Link>
                                        <span v-else class="block max-w-48 truncate text-xs font-semibold text-stone-400">{{ movement.product_name }}</span>
                                        <span class="mt-0.5 block font-mono text-[10px] text-stone-400">{{ movement.product_sku }}</span>
                                    </td>
                                    <td class="px-4 py-3.5"><Badge :tone="movement.type_tone" dot>{{ movement.type_label }}</Badge></td>
                                    <td class="px-4 py-3.5 text-sm font-extrabold tabular" :class="movement.is_in ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">{{ movement.sign }}{{ movement.quantity_formatted }}</td>
                                    <td class="px-4 py-3.5 text-xs text-stone-600 dark:text-stone-300">{{ movement.reason_label }}</td>
                                    <td class="px-4 py-3.5 text-xs text-stone-600 dark:text-stone-300">{{ movement.user_name ?? '—' }}</td>
                                    <td class="max-w-56 truncate px-5 py-3.5 text-xs text-stone-500 dark:text-stone-400">{{ movement.note || '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :paginator="movements" />
                </div>

                <div class="space-y-3 lg:hidden">
                    <article v-for="movement in movements.data" :key="movement.id" class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <Link v-if="movement.product_url" :href="movement.product_url" class="block truncate text-sm font-bold text-stone-900 dark:text-white">{{ movement.product_name }}</Link>
                                <span v-else class="block truncate text-sm font-bold text-stone-500 dark:text-stone-400">{{ movement.product_name }}</span>
                                <p class="mt-0.5 font-mono text-[10px] text-stone-400">{{ movement.product_sku }}</p>
                            </div>
                            <Badge :tone="movement.type_tone" dot>{{ movement.type_label }}</Badge>
                        </div>
                        <div class="mt-3 flex items-center justify-between gap-3 rounded-xl bg-stone-50 px-3 py-2.5 dark:bg-night-950">
                            <div>
                                <p class="text-[10px] font-semibold text-stone-400">الكمية</p>
                                <p class="mt-0.5 text-sm font-extrabold tabular" :class="movement.is_in ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">{{ movement.sign }}{{ movement.quantity_formatted }} <span class="text-[10px] font-medium text-stone-400">{{ movement.product_unit }}</span></p>
                            </div>
                            <div class="text-end">
                                <p class="text-[10px] font-semibold text-stone-400">التاريخ</p>
                                <p class="mt-0.5 text-[11px] font-semibold text-stone-700 tabular dark:text-stone-200">{{ movement.occurred_formatted }}</p>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between gap-3 text-[11px]">
                            <span class="text-stone-500 dark:text-stone-400">{{ movement.reason_label }} · {{ movement.user_name ?? '—' }}</span>
                            <span v-if="movement.note" class="max-w-[55%] truncate text-stone-400">{{ movement.note }}</span>
                        </div>
                    </article>
                    <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900"><Pagination :paginator="movements" /></div>
                </div>
            </template>
        </div>

        <Modal :open="movementOpen" title="تسجيل حركة مخزون" description="اختر المنتج ونوع الحركة والكمية." size="lg" @close="movementOpen = false">
            <MovementForm :products="products" @success="movementOpen = false" @cancel="movementOpen = false" />
        </Modal>
    </AppLayout>
</template>
