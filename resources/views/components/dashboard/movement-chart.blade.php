@use('App\Support\Format')

@props(['chart'])

<x-ui.card class="xl:col-span-2">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">حركة المخزون</h2>
            <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">آخر 7 أيام</p>
        </div>
        <div class="flex items-center gap-4 text-[11px] font-semibold text-stone-500 dark:text-stone-400">
            <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-brand-400"></span> إدخال</span>
            <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-maroon-500"></span> إخراج</span>
        </div>
    </div>

    <div class="relative mt-5 h-64 sm:h-72">
        <canvas data-chart="bar" data-chart-data="{{ json_encode($chart, JSON_UNESCAPED_UNICODE) }}" aria-label="مخطط حركة المخزون خلال آخر سبعة أيام" role="img"></canvas>
    </div>
</x-ui.card>
