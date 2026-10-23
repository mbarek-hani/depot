<x-layouts.app title="إضافة منتج">
    <x-layout.page-header
        title="إضافة منتج جديد"
        description="سجّل بيانات المنتج والأسعار والكمية الافتتاحية."
        eyebrow="المخزون"
    >
        <x-slot:actions>
            <x-ui.button :href="route('products.index')" variant="outline" icon="arrow-right">عودة للمنتجات</x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <div class="mt-6 max-w-4xl">
        <x-products.form
            :categories="$categories"
            :default-threshold="$defaultThreshold"
            :action="route('products.store')"
            submit-label="إضافة المنتج"
        />
    </div>
</x-layouts.app>
