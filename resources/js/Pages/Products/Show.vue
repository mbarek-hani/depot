<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Components/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import StatCard from '../../Components/StatCard.vue';
import Badge from '../../Components/Badge.vue';
import Icon from '../../Components/Icon.vue';
import UiButton from '../../Components/UiButton.vue';
import Pagination from '../../Components/Pagination.vue';
import EmptyState from '../../Components/EmptyState.vue';
import Modal from '../../Components/Modal.vue';
import MovementForm from '../../Components/MovementForm.vue';

const props = defineProps({
    product: { type: Object, required: true },
    batches: { type: Array, default: () => [] },
    movements: { type: Object, required: true },
    totals: { type: Object, required: true },
});

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.is_admin === true);

const movementOpen = ref(false);
const deleteOpen = ref(false);
const deleting = ref(false);

function destroy() {
    deleting.value = true;
    router.delete(route('products.destroy', props.product.id), {
        onFinish: () => {
            deleting.value = false;
        },
    });
}
</script>

<template>
    <AppLayout :title="product.name">
        <PageHeader :title="product.name" :description="`الرمز ${product.sku} · ${product.category_name ?? 'بدون صنف'}`" eyebrow="تفاصيل المنتج">
            <template #actions>
                <UiButton :href="route('products.index')" variant="outline" icon="arrow-right">المنتجات</UiButton>
                <UiButton v-if="isAdmin" variant="brand-soft" icon="arrows-right-left" @click="movementOpen = true">تسجيل حركة</UiButton>
                <UiButton v-if="isAdmin" :href="route('products.edit', product.id)" icon="pencil">تعديل</UiButton>
                <UiButton v-if="isAdmin" variant="danger-soft" size="icon" icon="trash" aria-label="حذف المنتج" @click="deleteOpen = true" />
            </template>
        </PageHeader>

        <div class="mt-6 space-y-4">
            <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
                <StatCard label="الكمية الحالية" :value="`${product.quantity_formatted} ${product.unit}`" hint="الكمية المتوفرة بالمخزون" />
                <StatCard label="حالة المخزون" :value="product.stock_label" :hint="`حد التنبيه: ${product.threshold_formatted}`" />
                <StatCard label="قيمة المخزون" :value="product.stock_cost_formatted" hint="بحسب سعر التكلفة" />
                <StatCard label="أقرب صلاحية" :value="product.expiry_date_formatted ?? 'غير محددة'" :hint="product.active_batches_count ? `${product.active_batches_count} دفعة نشطة` : 'لا توجد دفعات نشطة'" />
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                <div class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card xl:col-span-2 dark:border-night-800 dark:bg-night-900">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">بيانات المنتج</h2>
                            <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">الأسعار والتصنيف والملاحظات.</p>
                        </div>
                        <div class="flex flex-wrap justify-end gap-1">
                            <Badge :tone="product.stock_tone">{{ product.stock_label }}</Badge>
                            <Badge v-if="product.expiry_status !== 'none'" :tone="product.expiry_tone">{{ product.expiry_label }}</Badge>
                        </div>
                    </div>

                    <dl class="mt-5 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-[11px] font-semibold text-stone-400">الصنف</dt>
                            <dd class="mt-1 flex items-center gap-2 text-sm font-bold text-stone-800 dark:text-stone-100">
                                <span class="size-2.5 rounded-full" :class="product.category_color ?? 'bg-stone-300'"></span>
                                {{ product.category_name ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold text-stone-400">وحدة القياس</dt>
                            <dd class="mt-1 text-sm font-bold text-stone-800 dark:text-stone-100">{{ product.unit }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold text-stone-400">سعر التكلفة</dt>
                            <dd class="mt-1 text-sm font-bold text-stone-800 tabular dark:text-stone-100">{{ product.cost_formatted }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold text-stone-400">سعر البيع</dt>
                            <dd class="mt-1 text-sm font-bold text-stone-800 tabular dark:text-stone-100">{{ product.sale_formatted }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold text-stone-400">قيمة البيع المتوقعة</dt>
                            <dd class="mt-1 text-sm font-bold text-emerald-700 tabular dark:text-emerald-400">{{ product.stock_sale_formatted }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold text-stone-400">هامش الربح المتوقع</dt>
                            <dd class="mt-1 text-sm font-bold text-stone-800 tabular dark:text-stone-100">{{ product.margin_formatted }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-[11px] font-semibold text-stone-400">ملاحظات</dt>
                            <dd class="mt-1 text-sm leading-6 text-stone-600 dark:text-stone-300">{{ product.notes || 'لا توجد ملاحظات.' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card dark:border-night-800 dark:bg-night-900">
                    <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">ملخص الحركات</h2>
                    <div class="mt-4 space-y-3">
                        <div class="flex items-center justify-between rounded-xl bg-emerald-50 px-4 py-3 dark:bg-emerald-500/10">
                            <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">إجمالي الإدخال</span>
                            <span class="font-display text-lg font-extrabold text-emerald-700 tabular dark:text-emerald-300">+{{ totals.in_formatted }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl bg-rose-50 px-4 py-3 dark:bg-rose-500/10">
                            <span class="text-xs font-semibold text-rose-700 dark:text-rose-300">إجمالي الإخراج</span>
                            <span class="font-display text-lg font-extrabold text-rose-700 tabular dark:text-rose-300">-{{ totals.out_formatted }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl bg-stone-100 px-4 py-3 dark:bg-night-800">
                            <span class="text-xs font-semibold text-stone-600 dark:text-stone-300">الرصيد الحالي</span>
                            <span class="font-display text-lg font-extrabold text-stone-900 tabular dark:text-white">{{ product.quantity_formatted }}</span>
                        </div>
                    </div>

                </div>
            </div>

            <div>
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-extrabold text-stone-900 dark:text-white">دفعات المخزون النشطة</h2>
                        <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">الرصيد المتبقي لكل دفعة مرتبًا حسب الأقرب انتهاءً.</p>
                    </div>
                    <Badge tone="neutral">{{ batches.length }} دفعة</Badge>
                </div>

                <div v-if="batches.length" class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[36rem] text-start">
                            <thead>
                                <tr class="border-b border-stone-100 bg-stone-50/70 text-[11px] font-bold text-stone-500 dark:border-night-800 dark:bg-night-950/60 dark:text-stone-400">
                                    <th class="px-5 py-3.5 text-start">الدفعة</th>
                                    <th class="px-4 py-3.5 text-start">تاريخ الانتهاء</th>
                                    <th class="px-4 py-3.5 text-start">المتبقي</th>
                                    <th class="px-4 py-3.5 text-start">تاريخ الإدخال</th>
                                    <th class="px-5 py-3.5 text-start">الحالة</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 dark:divide-night-800">
                                <tr v-for="batch in batches" :key="batch.id" class="hover:bg-stone-50/60 dark:hover:bg-night-800/30">
                                    <td class="px-5 py-3.5 font-mono text-[11px] font-bold text-stone-500 dark:text-stone-400">#{{ batch.id }}</td>
                                    <td class="px-4 py-3.5 text-xs font-bold text-stone-700 dark:text-stone-200">{{ batch.expiry_date_formatted }}</td>
                                    <td class="px-4 py-3.5 text-sm font-extrabold text-stone-900 tabular dark:text-white">{{ batch.quantity_remaining_formatted }} <span class="text-[10px] font-medium text-stone-400">{{ product.unit }}</span></td>
                                    <td class="px-4 py-3.5 text-xs text-stone-600 dark:text-stone-300">{{ batch.occurred_formatted }}</td>
                                    <td class="px-5 py-3.5"><Badge :tone="batch.expiry_tone">{{ batch.expiry_label }}</Badge></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <EmptyState v-else icon="calendar" title="لا توجد دفعات نشطة" description="ستظهر هنا دفعات المخزون عند تسجيل حركة إدخال بكمية." class="rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900" />
            </div>

            <div>
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-extrabold text-stone-900 dark:text-white">سجل الحركات</h2>
                        <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">كل عمليات الإدخال والإخراج لهذا المنتج.</p>
                    </div>
                    <UiButton v-if="isAdmin" size="sm" variant="outline" icon="plus" @click="movementOpen = true">حركة جديدة</UiButton>
                </div>

                <EmptyState v-if="!movements.data.length" icon="arrows-right-left" title="لا توجد حركات" description="ستظهر هنا حركات المخزون الخاصة بهذا المنتج." class="rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900" />

                <template v-else>
                    <div class="hidden overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card lg:block dark:border-night-800 dark:bg-night-900">
                        <table class="w-full min-w-[44rem] text-start">
                            <thead>
                                <tr class="border-b border-stone-100 bg-stone-50/70 text-[11px] font-bold text-stone-500 dark:border-night-800 dark:bg-night-950/60 dark:text-stone-400">
                                    <th class="px-5 py-3.5 text-start">التاريخ</th>
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
                                    <td class="px-4 py-3.5"><Badge :tone="movement.type_tone" dot>{{ movement.type_label }}</Badge></td>
                                    <td class="px-4 py-3.5 text-sm font-extrabold tabular" :class="movement.is_in ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">{{ movement.sign }}{{ movement.quantity_formatted }}</td>
                                    <td class="px-4 py-3.5 text-xs text-stone-600 dark:text-stone-300">{{ movement.reason_label }}</td>
                                    <td class="px-4 py-3.5 text-xs text-stone-600 dark:text-stone-300">{{ movement.user_name ?? '—' }}</td>
                                    <td class="max-w-56 truncate px-5 py-3.5 text-xs text-stone-500 dark:text-stone-400">{{ movement.note || '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination :paginator="movements" />
                    </div>

                    <div class="space-y-3 lg:hidden">
                        <article v-for="movement in movements.data" :key="movement.id" class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-stone-900 dark:text-white">{{ movement.reason_label }}</p>
                                    <p class="mt-0.5 text-[10px] text-stone-400">{{ movement.user_name ?? '—' }}</p>
                                </div>
                                <Badge :tone="movement.type_tone" dot>{{ movement.type_label }}</Badge>
                            </div>
                            <div class="mt-3 flex items-center justify-between gap-3 rounded-xl bg-stone-50 px-3 py-2.5 dark:bg-night-950">
                                <div>
                                    <p class="text-[10px] font-semibold text-stone-400">الكمية</p>
                                    <p class="mt-0.5 text-sm font-extrabold tabular" :class="movement.is_in ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">{{ movement.sign }}{{ movement.quantity_formatted }}</p>
                                </div>
                                <div class="text-end">
                                    <p class="text-[10px] font-semibold text-stone-400">التاريخ</p>
                                    <p class="mt-0.5 text-[11px] font-semibold text-stone-700 tabular dark:text-stone-200">{{ movement.occurred_formatted }}</p>
                                </div>
                            </div>
                            <p v-if="movement.note" class="mt-3 text-[11px] text-stone-400">{{ movement.note }}</p>
                        </article>
                        <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900"><Pagination :paginator="movements" /></div>
                    </div>
                </template>
            </div>
        </div>

        <Modal :open="movementOpen" title="تسجيل حركة مخزون" description="أضف أو اسحب كمية من مخزون هذا المنتج." @close="movementOpen = false">
            <MovementForm :fixed-product="product" @success="movementOpen = false" @cancel="movementOpen = false" />
        </Modal>

        <Modal :open="deleteOpen" title="تأكيد حذف المنتج" size="sm" @close="deleteOpen = false">
            <div class="flex items-start gap-3 rounded-xl bg-rose-50 p-4 text-sm text-rose-800 dark:bg-rose-500/10 dark:text-rose-200">
                <Icon name="warning" :size="20" class="mt-0.5 shrink-0" />
                <p>سيتم أرشفة «{{ product.name }}». ستبقى الحركات محفوظة في السجل.</p>
            </div>
            <template #footer>
                <UiButton variant="outline" @click="deleteOpen = false">إلغاء</UiButton>
                <UiButton variant="danger" icon="trash" :disabled="deleting" @click="destroy">حذف المنتج</UiButton>
            </template>
        </Modal>
    </AppLayout>
</template>
