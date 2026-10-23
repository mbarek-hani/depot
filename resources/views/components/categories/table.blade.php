@use('App\Support\Format')

@props(['categories'])

<div class="hidden overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900 lg:block">
    <div class="overflow-x-auto scrollbar-thin">
        <table class="w-full min-w-[40rem]">
            <thead>
                <tr class="border-b border-stone-100 bg-stone-50/70 text-[11px] font-bold text-stone-500 dark:border-night-800 dark:bg-night-950/60 dark:text-stone-400">
                    <th class="px-5 py-3.5 text-start font-bold">الصنف</th>
                    <th class="px-4 py-3.5 text-start font-bold">المنتجات</th>
                    <th class="px-4 py-3.5 text-start font-bold">إجمالي الكميات</th>
                    <th class="px-5 py-3.5 text-end font-bold">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-night-800">
                @foreach ($categories as $category)
                    <tr class="transition hover:bg-stone-50/60 dark:hover:bg-night-800/30">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <span class="size-3 rounded-full {{ $category->colorClasses() }}"></span>
                                <div>
                                    <p class="text-xs font-bold text-stone-900 dark:text-white">{{ $category->name }}</p>
                                    @if ($category->description)
                                        <p class="mt-0.5 max-w-xs truncate text-[10px] text-stone-400">{{ $category->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="text-xs font-semibold text-stone-700 tabular dark:text-stone-200">{{ number_format($category->products_count) }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="text-xs font-semibold text-stone-700 tabular dark:text-stone-200">{{ Format::quantity($category->quantity_total) }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" x-on:click="$dispatch('open-modal', 'edit-category-{{ $category->id }}')" class="inline-flex size-9 items-center justify-center rounded-lg text-stone-500 transition hover:bg-stone-100 hover:text-stone-900 dark:hover:bg-night-800 dark:hover:text-white" aria-label="تعديل الصنف">
                                    <x-ui.icon name="pencil" size="size-4" />
                                </button>
                                @if ($category->all_products_count === 0)
                                    <button type="button" x-on:click="$dispatch('open-modal', 'delete-category-{{ $category->id }}')" class="inline-flex size-9 items-center justify-center rounded-lg text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-500/10" aria-label="حذف الصنف">
                                        <x-ui.icon name="trash" size="size-4" />
                                    </button>
                                @else
                                    <span class="inline-flex size-9 items-center justify-center rounded-lg text-stone-300 dark:text-stone-600" title="لا يمكن حذف صنف مرتبط بمنتجات">
                                        <x-ui.icon name="lock" size="size-4" />
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
