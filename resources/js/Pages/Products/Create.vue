<script setup>
import AppLayout from '../../Components/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import UiButton from '../../Components/UiButton.vue';
import ProductForm from '../../Components/ProductForm.vue';
import { route } from 'ziggy-js';

defineProps({
    categories: { type: Array, default: () => [] },
    defaultThreshold: [String, Number],
    currency: String,
});
</script>

<template>
    <AppLayout title="إضافة منتج">
        <PageHeader title="إضافة منتج جديد" description="سجّل بيانات المنتج والأسعار والكمية الافتتاحية." eyebrow="المخزون">
            <template #actions>
                <UiButton :href="route('products.index')" variant="outline" icon="arrow-right">عودة للمنتجات</UiButton>
            </template>
        </PageHeader>

        <div class="mt-6 max-w-4xl">
            <ProductForm :categories="categories" :default-threshold="defaultThreshold" :currency="currency" />
        </div>
    </AppLayout>
</template>
