@php
    $options = [
        ['value' => 'light', 'label' => 'فاتح', 'description' => 'واجهة نهارية مريحة', 'icon' => 'sun'],
        ['value' => 'dark', 'label' => 'داكن', 'description' => 'واجهة ليلية هادئة', 'icon' => 'moon'],
        ['value' => 'system', 'label' => 'النظام', 'description' => 'يتبع إعداد جهازك', 'icon' => 'computer'],
    ];
@endphp

<div
    x-data="{
        current: 'system',
        handler: null,
        init() {
            this.current = document.documentElement.dataset.theme || 'system';
            this.handler = (event) => (this.current = event.detail.theme);
            window.addEventListener('theme-changed', this.handler);
        },
        destroy() {
            window.removeEventListener('theme-changed', this.handler);
        },
        choose(theme) {
            this.current = theme;
            window.themeManager?.set(theme);
        },
    }"
    class="grid gap-3 sm:grid-cols-3"
>
    @foreach ($options as $option)
        <button
            type="button"
            x-on:click="choose('{{ $option['value'] }}')"
            :class="current === '{{ $option['value'] }}'
                ? 'border-brand-400 bg-brand-50 ring-2 ring-brand-400/30 dark:border-brand-400 dark:bg-brand-400/10'
                : 'border-stone-200 bg-white hover:border-stone-300 dark:border-night-800 dark:bg-night-950 dark:hover:border-stone-700'"
            class="flex flex-col items-start gap-3 rounded-2xl border p-4 text-start transition"
        >
            <span
                :class="current === '{{ $option['value'] }}'
                    ? 'bg-brand-400 text-night-950'
                    : 'bg-stone-100 text-stone-500 dark:bg-night-800 dark:text-stone-300'"
                class="flex size-10 items-center justify-center rounded-xl transition"
            >
                <x-ui.icon :name="$option['icon']" size="size-5" />
            </span>
            <span>
                <span class="block text-sm font-bold text-stone-900 dark:text-white">{{ $option['label'] }}</span>
                <span class="mt-0.5 block text-[11px] text-stone-500 dark:text-stone-400">{{ $option['description'] }}</span>
            </span>
        </button>
    @endforeach
</div>
