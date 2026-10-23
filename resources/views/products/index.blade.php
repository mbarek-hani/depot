@use('App\Support\Format')

<x-layouts.app title="المنتجات">
    <x-layout.page-header
        title="المنتجات"
        description="إدارة جميع المنتجات والكميات وأسعار المخزون."
        eyebrow="المخزون"
    >
        <x-slot:actions>
            @if (auth()->user()->isAdmin())
                <x-ui.button :href="route('products.create')" icon="plus">إضافة منتج</x-ui.button>
            @endif
        </x-slot:actions>
    </x-layout.page-header>

    <div class="mt-6 space-y-4">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-stone-200/80 bg-white p-3.5 shadow-card dark:border-night-800 dark:bg-night-900">
                <p class="text-[10px] font-semibold text-stone-500 dark:text-stone-400">إجمالي المنتجات</p>
                <p class="mt-1 text-lg font-extrabold text-stone-900 tabular dark:text-white">{{ number_format($summary['total']) }}</p>
            </div>
            <div class="rounded-xl border border-amber-200/70 bg-amber-50 p-3.5 dark:border-amber-500/20 dark:bg-amber-500/10">
                <p class="text-[10px] font-semibold text-amber-700 dark:text-amber-300">مخزون منخفض</p>
                <p class="mt-1 text-lg font-extrabold text-amber-800 tabular dark:text-amber-200">{{ number_format($summary['low']) }}</p>
            </div>
            <div class="rounded-xl border border-rose-200/70 bg-rose-50 p-3.5 dark:border-rose-500/20 dark:bg-rose-500/10">
                <p class="text-[10px] font-semibold text-rose-700 dark:text-rose-300">نفد المخزون</p>
                <p class="mt-1 text-lg font-extrabold text-rose-800 tabular dark:text-rose-200">{{ number_format($summary['out']) }}</p>
            </div>
            <div class="rounded-xl border border-stone-200/80 bg-white p-3.5 shadow-card dark:border-night-800 dark:bg-night-900">
                <p class="text-[10px] font-semibold text-stone-500 dark:text-stone-400">قريب الانتهاء</p>
                <p class="mt-1 text-lg font-extrabold text-stone-900 tabular dark:text-white">{{ number_format($summary['expiring']) }}</p>
            </div>
        </div>

        <x-products.filters :categories="$categories" :filters="$filters" />

        @if ($products->isEmpty())
            <div class="rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
                <x-ui.empty-state icon="cube" title="لا توجد منتجات مطابقة" description="جرّب تغيير الفلاتر أو أضف منتجًا جديدًا.">
                    <x-slot:actions>
                        <a href="{{ route('products.index') }}" class="text-xs font-bold text-brand-700 hover:underline dark:text-brand-300">مسح الفلاتر</a>
                        @if (auth()->user()->isAdmin())
                            <x-ui.button :href="route('products.create')" icon="plus">إضافة منتج</x-ui.button>
                        @endif
                    </x-slot:actions>
                </x-ui.empty-state>
            </div>
        @else
            <x-products.table :products="$products" />
            <x-products.cards :products="$products" />

            @if (auth()->user()->isAdmin())
                @foreach ($products as $product)
                    <x-ui.modal name="delete-product-{{ $product->id }}" title="تأكيد حذف المنتج" description="سيتم أرشفة المنتج ولن يظهر في القوائم." size="sm">
                        <div class="flex items-start gap-3 rounded-xl bg-rose-50 p-4 text-sm text-rose-800 dark:bg-rose-500/10 dark:text-rose-200">
                            <x-ui.icon name="warning" size="size-5" class="mt-0.5 shrink-0" />
                            <p>هل أنت متأكد من حذف «{{ $product->name }}»؟ ستبقى حركات المخزون محفوظة في السجل.</p>
                        </div>

                        <x-slot:footer>
                            <x-ui.button type="button" variant="outline" x-on:click="$dispatch('close-modal', 'delete-product-{{ $product->id }}')">إلغاء</x-ui.button>
                            <form method="POST" action="{{ route('products.destroy', $product) }}">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="danger" icon="trash">حذف المنتج</x-ui.button>
                            </form>
                        </x-slot:footer>
                    </x-ui.modal>
                @endforeach
            @endif
        @endif
    </div>
</x-layouts.app>
