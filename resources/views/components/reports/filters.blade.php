@props(['report', 'categories', 'filters'])

@php
    $statusOptions = match ($report['type']) {
        'stock' => ['all' => 'كل الحالات', 'available' => 'متوفر', 'low' => 'منخفض', 'out' => 'نفد'],
        'movements' => ['all' => 'كل الحركات', 'in' => 'إدخال', 'out' => 'إخراج'],
        'expiry' => ['all' => 'جميع المنتجات ضمن المدة', 'expiring' => 'قريبة الانتهاء', 'expired' => 'منتهية'],
        default => [],
    };

    $showDates = $report['type'] === 'movements';
@endphp

<x-ui.card>
    <form method="GET" action="{{ route('reports.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
        <input type="hidden" name="type" value="{{ $report['type'] }}">

        <div class="{{ $showDates ? 'lg:col-span-3' : 'lg:col-span-4' }}">
            <x-ui.select name="category_id" label="الصنف" :selected="$filters['category_id']" placeholder="جميع الأصناف">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </x-ui.select>
        </div>

        @if ($statusOptions !== [])
            <div class="{{ $showDates ? 'lg:col-span-2' : 'lg:col-span-4' }}">
                <x-ui.select name="status" label="الحالة" :selected="$filters['status']">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        @endif

        @if ($showDates)
            <div class="lg:col-span-2">
                <x-ui.input name="from" type="date" label="من تاريخ" :value="$filters['from']?->format('Y-m-d')" />
            </div>
            <div class="lg:col-span-2">
                <x-ui.input name="to" type="date" label="إلى تاريخ" :value="$filters['to']?->format('Y-m-d')" />
            </div>
        @endif

        <div class="flex items-center gap-2 lg:col-span-3">
            <x-ui.button type="submit" icon="filter" class="w-full">تحديث التقرير</x-ui.button>
            <x-ui.button :href="route('reports.index', ['type' => $report['type']])" variant="ghost">مسح</x-ui.button>
        </div>
    </form>
</x-ui.card>
