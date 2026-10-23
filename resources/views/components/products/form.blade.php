@use('App\Support\Format')

@props([
    'product' => null,
    'categories',
    'defaultThreshold',
    'action',
    'method' => 'POST',
    'submitLabel' => 'حفظ المنتج',
])

<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <div class="mb-5 flex items-center gap-3">
            <span class="flex size-10 items-center justify-center rounded-xl bg-brand-100 text-brand-800 dark:bg-brand-400/15 dark:text-brand-300">
                <x-ui.icon name="cube" size="size-5" />
            </span>
            <div>
                <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">بيانات المنتج</h2>
                <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">الاسم والتصنيف وتاريخ الصلاحية.</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-ui.input name="name" label="اسم المنتج" :value="$product?->name" placeholder="مثال: أرز بسمتي 5 كجم" required />
            </div>

            <x-ui.input
                name="sku"
                label="رمز المنتج"
                :value="$product?->sku"
                placeholder="PRD-00001"
                :required="$product !== null"
                hint="اتركه فارغًا ليتم إنشاؤه تلقائيًا."
            />

            <x-ui.select name="category_id" label="الصنف" :selected="$product?->category_id" placeholder="اختر الصنف" required>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $product?->category_id) === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.input name="unit" label="وحدة القياس" :value="$product?->unit" placeholder="قطعة، كرتون، كيس، كجم..." required />

            <x-ui.input name="expiry_date" label="تاريخ الانتهاء" type="date" :value="$product?->expiry_date?->format('Y-m-d')" hint="اتركه فارغًا للمنتجات التي لا تنتهي صلاحيتها." />
        </div>
    </x-ui.card>

    <x-ui.card>
        <div class="mb-5 flex items-center gap-3">
            <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">
                <x-ui.icon name="banknotes" size="size-5" />
            </span>
            <div>
                <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">الأسعار والمخزون</h2>
                <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">أسعار الوحدة وحد التنبيه للمخزون المنخفض.</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-ui.input name="cost_price" label="سعر التكلفة ({{ $currency }})" type="number" step="0.01" min="0" :value="$product?->cost_price ?? '0.00'" required />
            <x-ui.input name="sale_price" label="سعر البيع ({{ $currency }})" type="number" step="0.01" min="0" :value="$product?->sale_price ?? '0.00'" required />

            <x-ui.input
                name="low_stock_threshold"
                label="حد المخزون المنخفض"
                type="number"
                step="0.01"
                min="0"
                :value="$product?->low_stock_threshold"
                :hint="'اتركه فارغًا لاستخدام الافتراضي: '.Format::quantity($defaultThreshold)"
            />

            @if ($product)
                <div class="rounded-xl border border-stone-200 bg-stone-50 p-3.5 dark:border-night-800 dark:bg-night-950">
                    <p class="text-[11px] font-semibold text-stone-500 dark:text-stone-400">الكمية الحالية</p>
                    <p class="mt-1 font-display text-lg font-extrabold text-stone-900 tabular dark:text-white">{{ Format::quantity($product->quantity) }} {{ $product->unit }}</p>
                    <a href="{{ route('products.show', $product) }}" class="mt-1 inline-flex items-center gap-1 text-[10px] font-bold text-brand-700 hover:underline dark:text-brand-300">
                        <x-ui.icon name="arrows-right-left" size="size-3" />
                        تعديل الكمية عبر حركة مخزون
                    </a>
                </div>
            @else
                <x-ui.input
                    name="quantity"
                    label="الكمية الافتتاحية"
                    type="number"
                    step="0.01"
                    min="0"
                    value="0"
                    hint="سيتم تسجيلها كحركة إدخال برصيد افتتاحي."
                />
            @endif
        </div>
    </x-ui.card>

    <x-ui.card>
        <x-ui.textarea name="notes" label="ملاحظات" :value="$product?->notes" placeholder="أي تفاصيل إضافية عن المنتج..." />
    </x-ui.card>

    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
        <x-ui.button :href="$product ? route('products.show', $product) : route('products.index')" variant="outline">إلغاء</x-ui.button>
        <x-ui.button type="submit" icon="check">{{ $submitLabel }}</x-ui.button>
    </div>
</form>
