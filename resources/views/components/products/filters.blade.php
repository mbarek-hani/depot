@props(['categories', 'filters'])

<x-ui.card>
    <form method="GET" action="{{ route('products.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
        <div class="lg:col-span-4">
            <x-ui.input name="search" :value="$filters['search']" placeholder="ابحث بالاسم أو الرمز..." icon="search" class="[&_label]:sr-only" aria-label="البحث في المنتجات" />
        </div>

        <div class="lg:col-span-3">
            <x-ui.select name="category_id" :selected="$filters['category_id']" placeholder="جميع الأصناف" class="[&_label]:sr-only" aria-label="تصفية حسب الصنف">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </x-ui.select>
        </div>

        <div class="lg:col-span-2">
            <x-ui.select name="status" :selected="$filters['status']" placeholder="كل الحالات" class="[&_label]:sr-only" aria-label="تصفية حسب الحالة">
                <option value="available" @selected($filters['status'] === 'available')>متوفر</option>
                <option value="low" @selected($filters['status'] === 'low')>مخزون منخفض</option>
                <option value="out" @selected($filters['status'] === 'out')>نفد المخزون</option>
                <option value="expiring" @selected($filters['status'] === 'expiring')>قريب الانتهاء</option>
                <option value="expired" @selected($filters['status'] === 'expired')>منتهي الصلاحية</option>
            </x-ui.select>
        </div>

        <div class="lg:col-span-2">
            <x-ui.select name="sort" :selected="$filters['sort']" class="[&_label]:sr-only" aria-label="ترتيب المنتجات">
                <option value="created_at|desc" @selected($filters['sort'] === 'created_at|desc')>الأحدث</option>
                <option value="name|asc" @selected($filters['sort'] === 'name|asc')>الاسم</option>
                <option value="quantity|asc" @selected($filters['sort'] === 'quantity|asc')>الكمية الأقل</option>
                <option value="expiry_date|asc" @selected($filters['sort'] === 'expiry_date|asc')>الصلاحية الأقرب</option>
            </x-ui.select>
        </div>

        <div class="flex items-center gap-2 lg:col-span-1">
            <x-ui.button type="submit" size="md" class="w-full" aria-label="تطبيق التصفية">
                <x-ui.icon name="filter" size="size-4" />
                <span class="lg:sr-only">تصفية</span>
            </x-ui.button>
        </div>

        @if ($filters['search'] || $filters['category_id'] || $filters['status'] || $filters['sort'] !== 'created_at|desc')
            <div class="lg:col-span-12">
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-700 hover:underline dark:text-brand-300">
                    <x-ui.icon name="x" size="size-3.5" />
                    مسح الفلاتر
                </a>
            </div>
        @endif
    </form>
</x-ui.card>
