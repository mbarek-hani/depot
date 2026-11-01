<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import Field from './Field.vue';
import Icon from './Icon.vue';
import UiButton from './UiButton.vue';

const props = defineProps({
    product: Object,
    categories: { type: Array, default: () => [] },
    defaultThreshold: [String, Number],
    currency: { type: String, default: 'MAD' },
});

const form = useForm({
    name: props.product?.name ?? '',
    sku: props.product?.sku ?? '',
    category_id: props.product?.category_id ?? '',
    unit: props.product?.unit ?? '',
    expiry_date: '',
    cost_price: props.product?.cost_price ?? '0.00',
    sale_price: props.product?.sale_price ?? '0.00',
    low_stock_threshold: props.product?.low_stock_threshold ?? '',
    quantity: 0,
    notes: props.product?.notes ?? '',
});

const isEdit = computed(() => Boolean(props.product?.id));
const inputClass = 'h-11 w-full rounded-xl border border-stone-300 bg-white px-3.5 text-sm text-stone-900 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-950 dark:text-white';
const textareaClass = 'min-h-24 w-full rounded-xl border border-stone-300 bg-white px-3.5 py-3 text-sm text-stone-900 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-950 dark:text-white';

function submit() {
    if (isEdit.value) {
        form.patch(route('products.update', props.product.id), { preserveScroll: true });
        return;
    }

    form.post(route('products.store'));
}
</script>

<template>
    <form class="space-y-4" @submit.prevent="submit">
        <div class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card dark:border-night-800 dark:bg-night-900">
            <div class="mb-5 flex items-center gap-3">
                <span class="flex size-10 items-center justify-center rounded-xl bg-brand-100 text-brand-800 dark:bg-brand-400/15 dark:text-brand-300">
                    <Icon name="cube" :size="20" />
                </span>
                <div>
                    <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">بيانات المنتج</h2>
                    <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">الاسم والتصنيف ووحدة القياس.</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <Field label="اسم المنتج" :error="form.errors.name" required>
                        <input v-model="form.name" :class="inputClass" placeholder="مثال: أرز بسمتي 5 كجم" autofocus>
                    </Field>
                </div>

                <Field label="رمز المنتج" :error="form.errors.sku" :required="isEdit" hint="اتركه فارغًا ليتم إنشاؤه تلقائيًا.">
                    <input v-model="form.sku" :class="inputClass" placeholder="PRD-00001" dir="ltr">
                </Field>

                <Field label="الصنف" :error="form.errors.category_id" required>
                    <select v-model="form.category_id" :class="inputClass">
                        <option value="" disabled>اختر الصنف</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                </Field>

                <Field label="وحدة القياس" :error="form.errors.unit" required>
                    <input v-model="form.unit" :class="inputClass" placeholder="قطعة، كرتون، كيس، كجم...">
                </Field>

            </div>
        </div>

        <div class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card dark:border-night-800 dark:bg-night-900">
            <div class="mb-5 flex items-center gap-3">
                <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">
                    <Icon name="banknotes" :size="20" />
                </span>
                <div>
                    <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">الأسعار والمخزون</h2>
                    <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">أسعار الوحدة وحد التنبيه للمخزون المنخفض.</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <Field :label="`سعر التكلفة (${currency})`" :error="form.errors.cost_price" required>
                    <input v-model="form.cost_price" type="number" step="0.01" min="0" :class="inputClass">
                </Field>
                <Field :label="`سعر البيع (${currency})`" :error="form.errors.sale_price" required>
                    <input v-model="form.sale_price" type="number" step="0.01" min="0" :class="inputClass">
                </Field>

                <Field label="حد المخزون المنخفض" :error="form.errors.low_stock_threshold" :hint="`اتركه فارغًا لاستخدام الافتراضي: ${defaultThreshold}`">
                    <input v-model="form.low_stock_threshold" type="number" step="0.01" min="0" :class="inputClass">
                </Field>

                <div v-if="product" class="rounded-xl border border-stone-200 bg-stone-50 p-3.5 dark:border-night-800 dark:bg-night-950">
                    <p class="text-[11px] font-semibold text-stone-500 dark:text-stone-400">الكمية الحالية</p>
                    <p class="mt-1 font-display text-lg font-extrabold text-stone-900 tabular dark:text-white">{{ product.quantity_formatted }} {{ product.unit }}</p>
                    <p class="mt-1 flex items-center gap-1 text-[10px] font-bold text-brand-700 dark:text-brand-300">
                        <Icon name="arrows-right-left" :size="12" />
                        تعديل الكمية عبر حركة مخزون
                    </p>
                </div>
                <template v-else>
                    <Field label="الكمية الافتتاحية" :error="form.errors.quantity" hint="سيتم تسجيلها كحركة إدخال برصيد افتتاحي.">
                        <input v-model="form.quantity" type="number" step="0.01" min="0" :class="inputClass">
                    </Field>
                    <Field label="تاريخ انتهاء الدفعة" :error="form.errors.expiry_date" hint="اتركه فارغًا للدفعات التي لا تنتهي صلاحيتها.">
                        <input v-model="form.expiry_date" type="date" :class="inputClass" dir="ltr">
                    </Field>
                </template>
            </div>
        </div>

        <div class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card dark:border-night-800 dark:bg-night-900">
            <Field label="ملاحظات" :error="form.errors.notes">
                <textarea v-model="form.notes" :class="textareaClass" placeholder="أي تفاصيل إضافية عن المنتج..." rows="3"></textarea>
            </Field>
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <UiButton :href="product ? route('products.show', product.id) : route('products.index')" variant="outline">إلغاء</UiButton>
            <UiButton type="submit" icon="check" :disabled="form.processing">{{ product ? 'حفظ التعديلات' : 'إضافة المنتج' }}</UiButton>
        </div>
    </form>
</template>
