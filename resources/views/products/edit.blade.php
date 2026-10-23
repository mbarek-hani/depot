<x-layouts.app title="تعديل منتج">
    <x-layout.page-header
        :title="'تعديل: '.$product->name"
        :description="'الرمز '.$product->sku"
        eyebrow="المخزون"
    >
        <x-slot:actions>
            <x-ui.button :href="route('products.show', $product)" variant="outline" icon="arrow-right">عودة للمنتج</x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <div class="mt-6 max-w-4xl">
        <x-products.form
            :product="$product"
            :categories="$categories"
            :default-threshold="$defaultThreshold"
            :action="route('products.update', $product)"
            method="PATCH"
            submit-label="حفظ التعديلات"
        />
    </div>
</x-layouts.app>
