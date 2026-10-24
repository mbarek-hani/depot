@use('App\Support\Format')

@props(['chart', 'total' => 0])

<x-ui.card>
    <div>
        <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">توزيع المخزون</h2>
        <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">حسب الأصناف</p>
    </div>

    @if (count($chart['labels']))
        <div class="relative mx-auto mt-5 h-56 max-w-xs">
            <canvas class="max-w-full" data-chart="doughnut" data-chart-data="{{ json_encode($chart, JSON_UNESCAPED_UNICODE) }}" aria-label="توزيع الكميات حسب الأصناف" role="img"></canvas>
        </div>
        <div class="mt-4 rounded-xl bg-stone-50 p-3 text-center dark:bg-night-950">
            <p class="text-[11px] font-semibold text-stone-500 dark:text-stone-400">إجمالي الكميات</p>
            <p class="mt-0.5 font-display text-xl font-extrabold text-stone-900 tabular dark:text-white">{{ Format::quantity($total) }}</p>
        </div>
    @else
        <x-ui.empty-state icon="chart-bar" title="لا توجد بيانات" description="أضف منتجات وكميات لعرض توزيع المخزون." />
    @endif
</x-ui.card>
