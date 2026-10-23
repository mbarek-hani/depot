@use('App\Support\Format')

@props(['stats'])

<div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 xl:grid-cols-6">
    <x-ui.stat-card
        label="إجمالي المنتجات"
        :value="number_format($stats['products'])"
        icon="cube"
        tone="brand"
        :href="route('products.index')"
        hint="منتج مسجل"
    />

    <x-ui.stat-card
        label="إجمالي الكميات"
        :value="Format::quantity($stats['quantity'])"
        icon="archive"
        tone="info"
        :href="route('products.index')"
        :hint="'اليوم: +'.Format::quantity($stats['in_today']).' / -'.Format::quantity($stats['out_today'])"
    />

    <x-ui.stat-card
        label="مخزون منخفض"
        :value="number_format($stats['low_stock'])"
        icon="warning"
        tone="warning"
        :href="route('products.index', ['status' => 'low'])"
        hint="بحاجة إلى إعادة تعبئة"
    />

    <x-ui.stat-card
        label="نفد المخزون"
        :value="number_format($stats['out_of_stock'])"
        icon="x"
        tone="danger"
        :href="route('products.index', ['status' => 'out'])"
        hint="منتجات غير متوفرة"
    />

    <x-ui.stat-card
        label="قريب الانتهاء"
        :value="number_format($stats['expiring'])"
        icon="calendar"
        tone="warning"
        :href="route('products.index', ['status' => 'expiring'])"
        :hint="$stats['expired'].' منتهي الصلاحية'"
    />

    <x-ui.stat-card
        label="قيمة المخزون"
        :value="Format::money($stats['stock_cost'])"
        icon="banknotes"
        tone="success"
        :href="route('reports.index', ['type' => 'stock'])"
        hint="بحسب سعر التكلفة"
    />
</div>
