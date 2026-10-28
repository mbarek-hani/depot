<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import Field from './Field.vue';
import Icon from './Icon.vue';
import UiButton from './UiButton.vue';

const props = defineProps({
    products: { type: Array, default: () => [] },
    fixedProduct: Object,
});

const emit = defineEmits(['success', 'cancel']);

const reasons = [
    { value: 'company', label: 'الشركة' },
    { value: 'kitchen', label: 'المطبخ' },
    { value: 'administration', label: 'الإدارة' },
    { value: 'club', label: 'النادي' },
    { value: 'other', label: 'أخرى' },
];

function localDateTime() {
    const now = new Date();
    const offset = now.getTimezoneOffset() * 60000;

    return new Date(now.getTime() - offset).toISOString().slice(0, 16);
}

const form = useForm({
    product_id: props.fixedProduct?.id ?? '',
    type: 'in',
    reason: 'company',
    quantity: '',
    occurred_at: localDateTime(),
    note: '',
});

const currentProduct = computed(() => {
    if (props.fixedProduct) {
        return props.fixedProduct;
    }

    return props.products.find((product) => String(product.id) === String(form.product_id));
});

const inputClass = 'h-11 w-full rounded-xl border border-stone-300 bg-white px-3.5 text-sm text-stone-900 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-950 dark:text-white';
const textareaClass = 'min-h-20 w-full rounded-xl border border-stone-300 bg-white px-3.5 py-3 text-sm text-stone-900 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-950 dark:text-white';

function submit() {
    form.post(route('movements.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('quantity', 'note');
            emit('success');
        },
    });
}
</script>

<template>
    <form class="space-y-4" @submit.prevent="submit">
        <div v-if="fixedProduct" class="rounded-xl border border-brand-200 bg-brand-50 p-3.5 dark:border-brand-400/20 dark:bg-brand-400/10">
            <p class="text-[11px] font-semibold text-brand-800 dark:text-brand-200">المنتج</p>
            <p class="mt-0.5 text-sm font-extrabold text-stone-900 dark:text-white">{{ fixedProduct.name }}</p>
            <p class="mt-0.5 text-[11px] text-stone-500 dark:text-stone-400">الكمية الحالية: {{ fixedProduct.quantity_formatted }} {{ fixedProduct.unit }}</p>
        </div>

        <Field v-else label="المنتج" :error="form.errors.product_id" required>
            <select v-model="form.product_id" :class="inputClass">
                <option value="" disabled>اختر المنتج</option>
                <option v-for="product in products" :key="product.id" :value="product.id">
                    {{ product.name }} — {{ product.sku }} ({{ product.quantity_formatted }} {{ product.unit }})
                </option>
            </select>
        </Field>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-1.5">
                <p class="text-sm font-semibold text-stone-700 dark:text-stone-200">نوع الحركة <span class="text-rose-500">*</span></p>
                <div class="grid grid-cols-2 gap-2">
                    <label
                        v-for="type in [{ value: 'in', label: 'إدخال', icon: 'trending-up' }, { value: 'out', label: 'إخراج', icon: 'trending-down' }]"
                        :key="type.value"
                        class="cursor-pointer"
                    >
                        <input v-model="form.type" type="radio" :value="type.value" class="peer sr-only">
                        <span class="flex items-center justify-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm font-bold text-stone-600 transition peer-checked:border-brand-400 peer-checked:bg-brand-50 peer-checked:text-brand-900 dark:border-night-800 dark:bg-night-950 dark:text-stone-300 dark:peer-checked:border-brand-400/50 dark:peer-checked:bg-brand-400/10 dark:peer-checked:text-brand-200">
                            <Icon :name="type.icon" :size="16" />
                            {{ type.label }}
                        </span>
                    </label>
                </div>
                <p v-if="form.errors.type" class="text-[11px] font-medium text-rose-600">{{ form.errors.type }}</p>
            </div>

            <Field label="سبب الحركة" :error="form.errors.reason" required>
                <select v-model="form.reason" :class="inputClass">
                    <option v-for="reason in reasons" :key="reason.value" :value="reason.value">{{ reason.label }}</option>
                </select>
            </Field>

            <Field label="الكمية" :error="form.errors.quantity" required>
                <input v-model="form.quantity" type="number" step="0.01" min="0.01" :class="inputClass" placeholder="0.00">
            </Field>

            <Field label="تاريخ الحركة" :error="form.errors.occurred_at" required>
                <input v-model="form.occurred_at" type="datetime-local" :class="inputClass" dir="ltr">
            </Field>
        </div>

        <div v-if="currentProduct" class="rounded-xl bg-stone-50 px-3.5 py-2.5 text-[11px] font-semibold text-stone-500 dark:bg-night-950 dark:text-stone-400">
            الرصيد المتوقع بعد الحركة:
            <span class="font-extrabold text-stone-800 tabular dark:text-stone-200">
                {{ Math.max(0, Number(currentProduct.quantity) + (form.type === 'in' ? 1 : -1) * Number(form.quantity || 0)).toFixed(2) }}
                {{ currentProduct.unit }}
            </span>
        </div>

        <Field label="ملاحظات" :error="form.errors.note">
            <textarea v-model="form.note" :class="textareaClass" rows="3" placeholder="رقم أمر التوريد أو سبب الإخراج..."></textarea>
        </Field>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <UiButton variant="outline" @click="emit('cancel')">إلغاء</UiButton>
            <UiButton type="submit" icon="check" :disabled="form.processing">تسجيل الحركة</UiButton>
        </div>
    </form>
</template>
