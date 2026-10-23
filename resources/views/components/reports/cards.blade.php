@props(['report'])

<div class="space-y-3 lg:hidden">
    @foreach ($report['rows'] as $row)
        <article class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
            <h3 class="text-sm font-bold text-stone-900 dark:text-white">{{ $row[0] ?? '—' }}</h3>

            <dl class="mt-3 space-y-2">
                @foreach (array_slice($report['columns'], 1) as $index => $label)
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-[10px] font-semibold text-stone-400">{{ $label }}</dt>
                        <dd class="text-end text-[11px] font-semibold text-stone-700 dark:text-stone-200">{{ $row[$index + 1] ?? '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </article>
    @endforeach

    <div class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
        <div class="flex flex-wrap gap-x-6 gap-y-2">
            @foreach ($report['totals'] as $label => $value)
                <div>
                    <p class="text-[10px] font-semibold text-stone-400">{{ $label }}</p>
                    <p class="mt-0.5 text-xs font-extrabold text-stone-900 tabular dark:text-white">{{ $value }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
