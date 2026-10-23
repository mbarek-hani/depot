@props(['report'])

<div class="hidden overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900 lg:block">
    <div class="overflow-x-auto scrollbar-thin">
        <table class="w-full min-w-[52rem]">
            <thead>
                <tr class="border-b border-stone-100 bg-stone-50/70 text-[11px] font-bold text-stone-500 dark:border-night-800 dark:bg-night-950/60 dark:text-stone-400">
                    @foreach ($report['columns'] as $index => $column)
                        <th class="px-4 py-3.5 text-start font-bold first:ps-5 last:pe-5">{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-night-800">
                @foreach ($report['rows'] as $row)
                    <tr class="transition hover:bg-stone-50/60 dark:hover:bg-night-800/30">
                        @foreach ($row as $index => $cell)
                            <td @class([
                                'px-4 py-3 text-xs first:ps-5 last:pe-5',
                                'font-bold text-stone-900 dark:text-white' => $index === 0,
                                'text-stone-600 dark:text-stone-300' => $index !== 0,
                            ])>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t border-stone-200 bg-stone-50/80 dark:border-night-800 dark:bg-night-950/60">
                    <td colspan="{{ count($report['columns']) }}" class="px-5 py-3.5">
                        <div class="flex flex-wrap items-center gap-x-8 gap-y-2">
                            @foreach ($report['totals'] as $label => $value)
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-semibold text-stone-400">{{ $label }}</span>
                                    <span class="text-xs font-extrabold text-stone-900 tabular dark:text-white">{{ $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
