<x-layouts.app title="التقارير">
    @php
        $tabQuery = array_filter([
            'category_id' => request('category_id'),
            'from' => request('from'),
            'to' => request('to'),
        ]);
    @endphp

    <x-layout.page-header
        title="التقارير"
        description="تقارير المخزون والحركات والصلاحية جاهزة للعرض والتنزيل بصيغة PDF."
        eyebrow="التحليلات"
    >
        <x-slot:actions>
            <x-ui.button :href="route('reports.download', array_merge(request()->query(), ['type' => $report['type']]))" icon="download">تنزيل PDF</x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <div class="mt-6 space-y-4">
        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
            @foreach ($types as $key => $label)
                <a
                    href="{{ route('reports.index', array_merge($tabQuery, ['type' => $key])) }}"
                    @class([
                        'snap-start whitespace-nowrap rounded-xl px-4 py-2.5 text-xs font-bold transition',
                        'bg-brand-400 text-night-950 shadow-sm' => $report['type'] === $key,
                        'border border-stone-200 bg-white text-stone-600 hover:border-stone-300 dark:border-night-800 dark:bg-night-900 dark:text-stone-300 dark:hover:border-stone-700' => $report['type'] !== $key,
                    ])
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            @foreach ($report['summary'] as $item)
                <x-ui.stat-card :label="$item['label']" :value="$item['value']" :tone="$item['tone']" icon="chart-bar" />
            @endforeach
        </div>

        <x-reports.filters :report="$report" :categories="$categories" :filters="$filters" />

        <div class="flex flex-col gap-2 rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card dark:border-night-800 dark:bg-night-900 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div>
                <h2 class="font-display text-lg font-extrabold text-stone-900 dark:text-white">{{ $report['title'] }}</h2>
                <p class="mt-1 text-xs leading-6 text-stone-500 dark:text-stone-400">{{ $report['description'] }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-[10px] font-semibold text-stone-400">
                <x-ui.badge tone="neutral" icon="calendar">{{ $report['period'] }}</x-ui.badge>
                <x-ui.badge tone="brand">{{ number_format(count($report['rows'])) }} سجل</x-ui.badge>
            </div>
        </div>

        @if (count($report['rows']) === 0)
            <div class="rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
                <x-ui.empty-state icon="document" title="لا توجد بيانات في هذا التقرير" description="جرّب تغيير الفلاتر أو الفترة الزمنية." />
            </div>
        @else
            <x-reports.table :report="$report" />
            <x-reports.cards :report="$report" />
        @endif
    </div>
</x-layouts.app>
