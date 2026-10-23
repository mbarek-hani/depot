<aside class="fixed inset-y-0 start-0 z-40 hidden w-72 flex-col border-e border-stone-200 bg-white lg:flex dark:border-white/5 dark:bg-night-900">
    <div class="flex items-center px-5 py-5">
        <x-ui.logo size="lg" with-text text-class="text-stone-900 dark:text-white" />
    </div>

    <div class="mx-5 h-px bg-stone-200 dark:bg-white/5"></div>

    <x-layout.nav />

    <x-layout.user-card />
</aside>

<div
    x-show="sidebarOpen"
    x-cloak
    class="fixed inset-0 z-[60] lg:hidden"
    role="dialog"
    aria-modal="true"
>
    <div x-show="sidebarOpen" x-transition.opacity class="absolute inset-0 bg-night-950/60 backdrop-blur-sm" x-on:click="sidebarOpen = false" aria-hidden="true"></div>

    <aside
        x-show="sidebarOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute inset-y-0 start-0 flex w-[18rem] max-w-[85vw] flex-col border-e border-stone-200 bg-white shadow-pop dark:border-white/5 dark:bg-night-900"
    >
        <div class="flex items-center justify-between px-5 py-5">
            <x-ui.logo size="md" with-text text-class="text-stone-900 dark:text-white" />
            <button type="button" x-on:click="sidebarOpen = false" class="rounded-lg p-1.5 text-stone-500 transition hover:bg-stone-100 hover:text-stone-900 dark:text-stone-400 dark:hover:bg-white/10 dark:hover:text-white" aria-label="إغلاق القائمة">
                <x-ui.icon name="x" size="size-5" />
            </button>
        </div>

        <div class="mx-5 h-px bg-stone-200 dark:bg-white/5"></div>

        <x-layout.nav />

        <x-layout.user-card />
    </aside>
</div>
