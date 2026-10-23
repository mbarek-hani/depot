@use('App\Models\Category')

<x-layouts.app title="الأصناف">
    <x-layout.page-header
        title="الأصناف"
        description="نظّم المنتجات ضمن أصناف واضحة لتسهيل البحث والتقارير."
        eyebrow="الإدارة"
    />

    <div class="mt-6 grid gap-4 xl:grid-cols-3">
        <x-ui.card class="xl:col-span-1">
            <div class="mb-5 flex items-center gap-3">
                <span class="flex size-10 items-center justify-center rounded-xl bg-brand-100 text-brand-800 dark:bg-brand-400/15 dark:text-brand-300">
                    <x-ui.icon name="tag" size="size-5" />
                </span>
                <div>
                    <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">إضافة صنف</h2>
                    <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">أنشئ صنفًا جديدًا للمنتجات.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('categories.store') }}" class="space-y-4">
                @csrf
                <x-ui.input name="name" label="اسم الصنف" placeholder="مثال: معلبات" required />

                <x-ui.select name="color" label="لون الصنف" required>
                    @foreach (Category::COLORS as $value => $label)
                        <option value="{{ $value }}" @selected(old('color', 'brand') === $value)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.textarea name="description" label="الوصف" :rows="3" placeholder="وصف مختصر للصنف..." />

                <x-ui.button type="submit" icon="plus" class="w-full">إضافة الصنف</x-ui.button>
            </form>
        </x-ui.card>

        <div class="space-y-3 xl:col-span-2">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-stone-500 dark:text-stone-400">{{ number_format($categories->count()) }} صنف</p>
            </div>

            @if ($categories->isEmpty())
                <div class="rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
                    <x-ui.empty-state icon="tag" title="لا توجد أصناف" description="أضف أول صنف من النموذج المجاور." />
                </div>
            @else
                <x-categories.table :categories="$categories" />
                <x-categories.cards :categories="$categories" />
            @endif
        </div>
    </div>

    @foreach ($categories as $category)
        <x-ui.modal name="edit-category-{{ $category->id }}" title="تعديل الصنف" size="sm">
            <form method="POST" action="{{ route('categories.update', $category) }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <x-ui.input name="name" :id="'edit-name-'.$category->id" label="اسم الصنف" :value="$category->name" required />
                <x-ui.select name="color" :id="'edit-color-'.$category->id" label="لون الصنف" :selected="$category->color" required>
                    @foreach (Category::COLORS as $value => $label)
                        <option value="{{ $value }}" @selected($category->color === $value)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.textarea name="description" :id="'edit-description-'.$category->id" label="الوصف" :value="$category->description" :rows="3" />

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-ui.button type="button" variant="outline" x-on:click="$dispatch('close-modal', 'edit-category-{{ $category->id }}')">إلغاء</x-ui.button>
                    <x-ui.button type="submit" icon="check">حفظ التعديلات</x-ui.button>
                </div>
            </form>
        </x-ui.modal>

        @if ($category->all_products_count === 0)
            <x-ui.modal name="delete-category-{{ $category->id }}" title="تأكيد حذف الصنف" size="sm">
                <div class="flex items-start gap-3 rounded-xl bg-rose-50 p-4 text-sm text-rose-800 dark:bg-rose-500/10 dark:text-rose-200">
                    <x-ui.icon name="warning" size="size-5" class="mt-0.5 shrink-0" />
                    <p>هل أنت متأكد من حذف صنف «{{ $category->name }}»؟ لا يمكن التراجع عن هذا الإجراء.</p>
                </div>
                <x-slot:footer>
                    <x-ui.button type="button" variant="outline" x-on:click="$dispatch('close-modal', 'delete-category-{{ $category->id }}')">إلغاء</x-ui.button>
                    <form method="POST" action="{{ route('categories.destroy', $category) }}">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="danger" icon="trash">حذف الصنف</x-ui.button>
                    </form>
                </x-slot:footer>
            </x-ui.modal>
        @endif
    @endforeach
</x-layouts.app>
