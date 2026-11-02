<script setup>
import { computed, reactive, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Components/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import UiButton from '../../Components/UiButton.vue';
import Badge from '../../Components/Badge.vue';
import Icon from '../../Components/Icon.vue';
import Pagination from '../../Components/Pagination.vue';
import EmptyState from '../../Components/EmptyState.vue';
import Modal from '../../Components/Modal.vue';

const props = defineProps({
    products: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, required: true },
    summary: { type: Object, required: true },
});

const filters = reactive({
    search: props.filters.search ?? '',
    category_id: props.filters.category_id ?? '',
    status: props.filters.status ?? '',
    sort: props.filters.sort ?? 'created_at|desc',
});

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.is_admin === true);

const selected = ref(null);
const deleting = ref(false);

const inputClass = 'h-10 w-full rounded-xl border border-stone-300 bg-white px-3.5 text-xs text-stone-900 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-950 dark:text-white';

function apply() {
    router.get(route('products.index'), { ...filters }, { preserveState: true, replace: true });
}

function clear() {
    router.get(route('products.index'));
}

function destroy() {
    if (!selected.value) {
        return;
    }

    deleting.value = true;
    router.delete(route('products.destroy', selected.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            selected.value = null;
        },
    });
}
</script>

<template>
    <AppLayout title="المنتجات">
        <PageHeader title="المنتجات" description="إدارة جميع المنتجات والكميات وأسعار المخزون." eyebrow="المخزون">
            <template #actions>
                <UiButton v-if="isAdmin" :href="route('products.create')" icon="plus">إضافة منتج</UiButton>
            </template>
        </PageHeader>

        <div class="mt-6 space-y-4">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-xl border border-stone-200/80 bg-white p-3.5 shadow-card dark:border-night-800 dark:bg-night-900">
                    <p class="text-[10px] font-semibold text-stone-500 dark:text-stone-400">إجمالي المنتجات</p>
                    <p class="mt-1 text-lg font-extrabold text-stone-900 tabular dark:text-white">{{ summary.total }}</p>
                </div>
                <div class="rounded-xl border border-stone-200/80 bg-white p-3.5 shadow-card dark:border-night-800 dark:bg-night-900">
                    <p class="text-[10px] font-semibold text-stone-500 dark:text-stone-400">مخزون منخفض</p>
                    <p class="mt-1 text-lg font-extrabold text-stone-900 tabular dark:text-white">{{ summary.low }}</p>
                </div>
                <div class="rounded-xl border border-stone-200/80 bg-white p-3.5 shadow-card dark:border-night-800 dark:bg-night-900">
                    <p class="text-[10px] font-semibold text-stone-500 dark:text-stone-400">نفد المخزون</p>
                    <p class="mt-1 text-lg font-extrabold text-stone-900 tabular dark:text-white">{{ summary.out }}</p>
                </div>
                <div class="rounded-xl border border-stone-200/80 bg-white p-3.5 shadow-card dark:border-night-800 dark:bg-night-900">
                    <p class="text-[10px] font-semibold text-stone-500 dark:text-stone-400">دفعات قريبة الانتهاء</p>
                    <p class="mt-1 text-lg font-extrabold text-stone-900 tabular dark:text-white">{{ summary.expiring }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
                <form class="grid gap-3 sm:grid-cols-2 lg:flex lg:flex-wrap lg:items-end" @submit.prevent="apply">
                    <div class="relative sm:col-span-2 lg:min-w-56 lg:flex-1">
                        <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-stone-400"><Icon name="search" :size="16" /></span>
                        <input v-model="filters.search" :class="inputClass" class="ps-9" placeholder="ابحث بالاسم أو الرمز..." aria-label="البحث في المنتجات">
                    </div>
                    <select v-model="filters.category_id" :class="inputClass" class="lg:w-44" aria-label="تصفية حسب الصنف">
                        <option value="">جميع الأصناف</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                    <select v-model="filters.status" :class="inputClass" class="lg:w-36" aria-label="تصفية حسب الحالة">
                        <option value="">كل الحالات</option>
                        <option value="available">متوفر</option>
                        <option value="low">مخزون منخفض</option>
                        <option value="out">نفد المخزون</option>
                        <option value="expiring">قريب الانتهاء</option>
                        <option value="expired">منتهي الصلاحية</option>
                    </select>
                    <select v-model="filters.sort" :class="inputClass" class="lg:w-40" aria-label="ترتيب المنتجات">
                        <option value="created_at|desc">الأحدث</option>
                        <option value="name|asc">الاسم</option>
                        <option value="quantity|asc">الكمية الأقل</option>
                        <option value="expiry_date|asc">الصلاحية الأقرب</option>
                    </select>
                    <div class="flex items-center gap-2 sm:col-span-2 lg:w-auto lg:shrink-0">
                        <UiButton type="submit" icon="filter" class="flex-1 lg:flex-none">تصفية</UiButton>
                        <UiButton variant="ghost" @click="clear">مسح</UiButton>
                    </div>
                </form>

            </div>

            <EmptyState v-if="!products.data.length" icon="cube" title="لا توجد منتجات مطابقة" description="جرّب تغيير الفلاتر أو أضف منتجًا جديدًا." class="rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
                <template #actions>
                    <UiButton v-if="isAdmin" :href="route('products.create')" icon="plus">إضافة منتج</UiButton>
                </template>
            </EmptyState>

            <template v-else>
                <div class="hidden overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card lg:block dark:border-night-800 dark:bg-night-900">
                    <div class="overflow-x-auto scrollbar-thin">
                        <table class="w-full min-w-[56rem] text-start">
                            <thead>
                                <tr class="border-b border-stone-100 bg-stone-50/70 text-[11px] font-bold text-stone-500 dark:border-night-800 dark:bg-night-950/60 dark:text-stone-400">
                                    <th class="px-5 py-3.5 text-start">المنتج</th>
                                    <th class="px-4 py-3.5 text-start">الصنف</th>
                                    <th class="px-4 py-3.5 text-start">الكمية</th>
                                    <th class="px-4 py-3.5 text-start">الحالة</th>
                                    <th class="px-4 py-3.5 text-start">الصلاحية</th>
                                    <th class="px-4 py-3.5 text-start">قيمة السطر</th>
                                    <th class="px-5 py-3.5 text-end">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 dark:divide-night-800">
                                <tr v-for="product in products.data" :key="product.key" class="transition hover:bg-stone-50/60 dark:hover:bg-night-800/30">
                                    <td class="px-5 py-3.5">
                                        <Link :href="route('products.show', product.id)" class="group flex items-center gap-3">
                                            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-400/10 dark:text-brand-300"><Icon name="cube" :size="18" /></span>
                                            <span class="min-w-0">
                                                <span class="block max-w-56 truncate text-xs font-bold text-stone-900 group-hover:text-brand-700 dark:text-white dark:group-hover:text-brand-300">{{ product.name }}</span>
                                                <span class="mt-0.5 block font-mono text-[10px] text-stone-400">{{ product.sku }}<template v-if="product.batch_id"> · دفعة #{{ product.batch_id }}</template></span>
                                            </span>
                                        </Link>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center gap-1.5 text-xs text-stone-600 dark:text-stone-300">
                                            <span class="size-2 rounded-full" :class="product.category_color"></span>
                                            {{ product.category_name ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <p class="text-xs font-bold text-stone-900 tabular dark:text-white">{{ product.quantity_formatted }} <span class="text-[10px] font-medium text-stone-400">{{ product.unit }}</span></p>
                                        <p class="mt-0.5 text-[10px] text-stone-400">إجمالي المنتج: {{ product.product_quantity_formatted }} · الحد: {{ product.threshold_formatted }}</p>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="flex flex-wrap gap-1">
                                            <Badge :tone="product.stock_tone">{{ product.stock_label }}</Badge>
                                            <Badge v-if="product.expiry_status !== 'none'" :tone="product.expiry_tone">{{ product.expiry_label }}</Badge>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <p v-if="product.expiry_date" class="text-xs font-semibold text-stone-700 dark:text-stone-200">{{ product.expiry_date_formatted }}</p>
                                        <span v-else class="text-xs text-stone-400">—</span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <p class="text-xs font-bold text-stone-900 tabular dark:text-white">{{ product.stock_cost_formatted }}</p>
                                        <p class="mt-0.5 text-[10px] text-stone-400">بيع: {{ product.stock_sale_formatted }}</p>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center justify-end gap-1">
                                            <UiButton :href="route('products.show', product.id)" variant="ghost" size="icon" icon="eye" aria-label="عرض المنتج" />
                                            <UiButton v-if="isAdmin" :href="route('products.edit', product.id)" variant="ghost" size="icon" icon="pencil" aria-label="تعديل المنتج" />
                                            <button v-if="isAdmin" type="button" class="inline-flex size-10 items-center justify-center rounded-xl text-rose-500 transition hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-500/10" aria-label="حذف المنتج" @click="selected = product">
                                                <Icon name="trash" :size="18" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :paginator="products" />
                </div>

                <div class="space-y-3 lg:hidden">
                    <article v-for="product in products.data" :key="product.key" class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
                        <div class="flex items-start justify-between gap-3">
                            <Link :href="route('products.show', product.id)" class="flex min-w-0 items-center gap-3">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-400/10 dark:text-brand-300"><Icon name="cube" :size="20" /></span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-bold text-stone-900 dark:text-white">{{ product.name }}</span>
                                    <span class="mt-0.5 block font-mono text-[10px] text-stone-400">{{ product.sku }}<template v-if="product.batch_id"> · دفعة #{{ product.batch_id }}</template></span>
                                </span>
                            </Link>
                            <div class="flex flex-wrap justify-end gap-1">
                                <Badge :tone="product.stock_tone">{{ product.stock_label }}</Badge>
                                <Badge v-if="product.expiry_status !== 'none'" :tone="product.expiry_tone">{{ product.expiry_label }}</Badge>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 rounded-xl bg-stone-50 p-3 dark:bg-night-950">
                            <div>
                                <p class="text-[10px] font-semibold text-stone-400">كمية الدفعة</p>
                                <p class="mt-0.5 text-xs font-extrabold text-stone-900 tabular dark:text-white">{{ product.quantity_formatted }} {{ product.unit }}</p>
                                <p class="mt-0.5 text-[10px] text-stone-400">الإجمالي: {{ product.product_quantity_formatted }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-stone-400">الصنف</p>
                                <p class="mt-0.5 truncate text-xs font-bold text-stone-700 dark:text-stone-200">{{ product.category_name ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-stone-400">الصلاحية</p>
                                <p class="mt-0.5 text-xs font-bold text-stone-700 dark:text-stone-200">{{ product.expiry_date_formatted ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-stone-400">قيمة السطر</p>
                                <p class="mt-0.5 text-xs font-bold text-stone-900 tabular dark:text-white">{{ product.stock_cost_formatted }}</p>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-end gap-2">
                            <UiButton :href="route('products.show', product.id)" variant="outline" size="sm" icon="eye">عرض</UiButton>
                            <UiButton v-if="isAdmin" :href="route('products.edit', product.id)" variant="brand-soft" size="sm" icon="pencil">تعديل</UiButton>
                            <button v-if="isAdmin" type="button" class="inline-flex size-9 items-center justify-center rounded-xl text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-500/10" aria-label="حذف المنتج" @click="selected = product">
                                <Icon name="trash" :size="16" />
                            </button>
                        </div>
                    </article>

                    <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
                        <Pagination :paginator="products" />
                    </div>
                </div>
            </template>
        </div>

        <Modal :open="Boolean(selected)" title="تأكيد حذف المنتج" size="sm" @close="selected = null">
            <div v-if="selected" class="flex items-start gap-3 rounded-xl bg-rose-50 p-4 text-sm text-rose-800 dark:bg-rose-500/10 dark:text-rose-200">
                <Icon name="warning" :size="20" class="mt-0.5 shrink-0" />
                <p>هل أنت متأكد من حذف «{{ selected.name }}»؟ سيتم حذف المنتج وكل حركات المخزون المرتبطة به نهائيًا.</p>
            </div>
            <template #footer>
                <UiButton variant="outline" @click="selected = null">إلغاء</UiButton>
                <UiButton variant="danger" icon="trash" :disabled="deleting" @click="destroy">حذف المنتج</UiButton>
            </template>
        </Modal>
    </AppLayout>
</template>
