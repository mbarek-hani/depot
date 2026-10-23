@props(['filters', 'categories'])

<x-ui.card>
    <form method="GET" action="{{ route('movements.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
        <div class="lg:col-span-3">
            <x-ui.input name="search" :value="$filters['search']" placeholder="ابحث عن منتج أو رمز..." icon="search" aria-label="البحث في الحركات" />
        </div>

        <div class="lg:col-span-2">
            <x-ui.select name="type" :selected="$filters['type']" placeholder="كل الأنواع" aria-label="نوع الحركة">
                <option value="in" @selected($filters['type'] === 'in')>إدخال</option>
                <option value="out" @selected($filters['type'] === 'out')>إخراج</option>
            </x-ui.select>
        </div>

        <div class="lg:col-span-3">
            <x-ui.select name="category_id" :selected="$filters['category_id']" placeholder="جميع الأصناف" aria-label="الصنف">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </x-ui.select>
        </div>

        <div class="lg:col-span-2">
            <x-ui.input name="from" type="date" :value="$filters['from']?->format('Y-m-d')" label="من تاريخ" />
        </div>

        <div class="lg:col-span-2">
            <x-ui.input name="to" type="date" :value="$filters['to']?->format('Y-m-d')" label="إلى تاريخ" />
        </div>

        <div class="flex items-center gap-2 lg:col-span-12">
            <x-ui.button type="submit" icon="filter">تطبيق</x-ui.button>
            <x-ui.button :href="route('movements.index')" variant="ghost">إعادة ضبط</x-ui.button>
        </div>
    </form>
</x-ui.card>
