@use('App\Support\Format')

@props(['categories'])

<div class="space-y-3 lg:hidden">
    @foreach ($categories as $category)
        <article class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
            <div class="flex items-start justify-between gap-3">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="size-3 shrink-0 rounded-full {{ $category->colorClasses() }}"></span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-stone-900 dark:text-white">{{ $category->name }}</p>
                        <p class="mt-0.5 text-[10px] text-stone-400">{{ $category->description ?: 'بدون وصف' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" x-on:click="$dispatch('open-modal', 'edit-category-{{ $category->id }}')" class="flex size-9 items-center justify-center rounded-lg text-stone-500 transition hover:bg-stone-100 dark:hover:bg-night-800 dark:hover:text-white" aria-label="تعديل الصنف">
                        <x-ui.icon name="pencil" size="size-4" />
                    </button>
                    @if ($category->all_products_count === 0)
                        <button type="button" x-on:click="$dispatch('open-modal', 'delete-category-{{ $category->id }}')" class="flex size-9 items-center justify-center rounded-lg text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-500/10" aria-label="حذف الصنف">
                            <x-ui.icon name="trash" size="size-4" />
                        </button>
                    @endif
                </div>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-3 rounded-xl bg-stone-50 p-3 dark:bg-night-950">
                <div>
                    <p class="text-[10px] font-semibold text-stone-400">المنتجات</p>
                    <p class="mt-0.5 text-xs font-bold text-stone-800 tabular dark:text-stone-100">{{ number_format($category->products_count) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-stone-400">إجمالي الكميات</p>
                    <p class="mt-0.5 text-xs font-bold text-stone-800 tabular dark:text-stone-100">{{ Format::quantity($category->quantity_total) }}</p>
                </div>
            </div>
        </article>
    @endforeach
</div>
