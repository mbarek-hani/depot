@use('App\Enums\MovementReason')
@use('App\Enums\StockMovementType')
@use('App\Support\Format')

@props([
    'products' => collect(),
    'fixedProduct' => null,
])

@php
    $selectedType = old('type', 'in');
@endphp

<form method="POST" action="{{ route('movements.store') }}" class="space-y-4" x-data="{ type: '{{ $selectedType }}' }">
    @csrf

    @if ($fixedProduct)
        <input type="hidden" name="product_id" value="{{ $fixedProduct->id }}">
        <div class="rounded-xl border border-brand-200 bg-brand-50 p-3.5 dark:border-brand-400/20 dark:bg-brand-400/10">
            <p class="text-[11px] font-semibold text-brand-800 dark:text-brand-200">المنتج</p>
            <p class="mt-0.5 text-sm font-extrabold text-stone-900 dark:text-white">{{ $fixedProduct->name }}</p>
            <p class="mt-0.5 text-[11px] text-stone-500 dark:text-stone-400">الكمية الحالية: {{ Format::quantity($fixedProduct->quantity) }} {{ $fixedProduct->unit }}</p>
        </div>
    @else
        <x-ui.select name="product_id" label="المنتج" :selected="old('product_id')" placeholder="اختر المنتج" required>
            @foreach ($products as $product)
                <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>
                    {{ $product->name }} — {{ $product->sku }} ({{ Format::quantity($product->quantity) }} {{ $product->unit }})
                </option>
            @endforeach
        </x-ui.select>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-stone-700 dark:text-stone-200">نوع الحركة <span class="text-rose-500">*</span></label>
            <div class="grid grid-cols-2 gap-2">
                @foreach (StockMovementType::cases() as $type)
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="{{ $type->value }}" x-model="type" class="peer sr-only">
                        <span @class([
                            'flex items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-sm font-bold transition',
                            'border-stone-200 bg-white text-stone-600 peer-checked:border-brand-400 peer-checked:bg-brand-50 peer-checked:text-brand-900 dark:border-night-800 dark:bg-night-950 dark:text-stone-300 dark:peer-checked:border-brand-400/50 dark:peer-checked:bg-brand-400/10 dark:peer-checked:text-brand-200',
                        ])>
                            <x-ui.icon :name="$type === StockMovementType::In ? 'trending-up' : 'trending-down'" size="size-4" />
                            {{ $type->label() }}
                        </span>
                    </label>
                @endforeach
            </div>
            @error('type')
                <p class="text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-1.5">
            <label for="movement-reason" class="block text-sm font-semibold text-stone-700 dark:text-stone-200">سبب الحركة <span class="text-rose-500">*</span></label>

            @foreach (StockMovementType::cases() as $type)
                <select
                    name="reason"
                    id="movement-reason-{{ $type->value }}"
                    x-show="type === '{{ $type->value }}'"
                    :disabled="type !== '{{ $type->value }}'"
                    required
                    class="h-11 w-full rounded-xl border border-stone-300 bg-white px-3.5 text-sm text-stone-900 shadow-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-950 dark:text-white"
                >
                    @foreach (MovementReason::forType($type) as $reason)
                        <option value="{{ $reason->value }}" @selected(old('reason') === $reason->value)>{{ $reason->label() }}</option>
                    @endforeach
                </select>
            @endforeach

            @error('reason')
                <p class="text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <x-ui.input name="quantity" label="الكمية" type="number" step="0.01" min="0.01" placeholder="0.00" required />
        <x-ui.input name="occurred_at" label="تاريخ الحركة" type="datetime-local" :value="old('occurred_at', now()->format('Y-m-d\TH:i'))" required />
    </div>

    <x-ui.textarea name="note" label="ملاحظات" :rows="3" placeholder="رقم أمر التوريد أو سبب الإخراج..." />

    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
        <x-ui.button type="button" variant="outline" x-on:click="$dispatch('close-modal', 'stock-movement-modal')">إلغاء</x-ui.button>
        <x-ui.button type="submit" icon="check">تسجيل الحركة</x-ui.button>
    </div>
</form>
