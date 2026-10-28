<script setup>
import AppLayout from '../../Components/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import UiButton from '../../Components/UiButton.vue';
import ProductForm from '../../Components/ProductForm.vue';
import { route } from 'ziggy-js';

const props = defineProps({
    product: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    defaultThreshold: [String, Number],
    currency: String,
});
</script>

<template>
    <AppLayout :title="`تعديل: ${product.name}`">
        <PageHeader :title="`تعديل: ${product.name}`" :description="`الرمز ${product.sku}`" eyebrow="المخزون">
            <template #actions>
                <UiButton :href="route('products.show', product.id)" variant="outline" icon="arrow-right">عودة للمنتج</UiButton>
            </template>
        </PageHeader>

        <div class="mt-6 max-w-4xl">
            <ProductForm :product="product" :categories="categories" :default-threshold="defaultThreshold" :currency="currency" />
        </div>
    </AppLayout>
</template>
