@props(['label' => false])

<button
    type="button"
    x-data
    x-on:click="window.themeManager.toggle()"
    class="inline-flex size-10 items-center justify-center rounded-xl border border-stone-200 bg-white text-stone-600 transition hover:border-stone-300 hover:text-stone-900 dark:border-night-800 dark:bg-night-900 dark:text-stone-300 dark:hover:border-stone-700 dark:hover:text-white"
    aria-label="تبديل الوضع الليلي"
    title="تبديل المظهر"
>
    <x-ui.icon name="moon" size="size-5" class="dark:hidden" />
    <x-ui.icon name="sun" size="size-5" class="hidden dark:block" />
    @if ($label)
        <span class="sr-only">تبديل المظهر</span>
    @endif
</button>
