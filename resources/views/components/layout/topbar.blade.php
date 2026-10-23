<header class="sticky top-0 z-30 border-b border-stone-200/70 bg-cream-100/85 backdrop-blur-lg dark:border-night-800 dark:bg-night-950/85">
    <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
        <button
            type="button"
            x-on:click="sidebarOpen = true"
            class="inline-flex size-10 items-center justify-center rounded-xl border border-stone-200 bg-white text-stone-600 transition hover:text-stone-900 dark:border-night-800 dark:bg-night-900 dark:text-stone-300 dark:hover:text-white lg:hidden"
            aria-label="فتح القائمة"
        >
            <x-ui.icon name="menu" size="size-5" />
        </button>

        <div class="lg:hidden">
            <x-ui.logo size="sm" />
        </div>

        <form method="GET" action="{{ route('products.index') }}" class="relative hidden max-w-sm flex-1 sm:block">
            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-stone-400">
                <x-ui.icon name="search" size="size-4.5" />
            </span>
            <input
                type="search"
                name="search"
                class="h-10 w-full rounded-xl border border-stone-200 bg-white ps-10 pe-3 text-sm text-stone-900 outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-900 dark:text-white dark:placeholder:text-stone-500"
                placeholder="ابحث عن منتج أو رمز..."
                aria-label="البحث في المنتجات"
            >
        </form>

        <div class="ms-auto flex items-center gap-2">
            <p class="me-1 hidden text-xs font-medium text-stone-500 dark:text-stone-400 xl:block">
                {{ now()->translatedFormat('l، j F Y') }}
            </p>

            <x-ui.theme-toggle />

            <x-layout.notification-bell :latest="$latestNotifications" :unread="$unreadCount" />

            <x-ui.dropdown width="w-60">
                <x-slot:trigger>
                    <button type="button" class="flex items-center gap-2 rounded-xl border border-stone-200 bg-white p-1 ps-2 transition hover:border-stone-300 dark:border-night-800 dark:bg-night-900 dark:hover:border-stone-700" aria-label="قائمة المستخدم">
                        <span class="hidden max-w-28 truncate text-xs font-bold text-stone-700 dark:text-stone-200 sm:block">{{ auth()->user()->name }}</span>
                        <x-ui.avatar :name="auth()->user()->name" size="size-8" />
                        <x-ui.icon name="chevron-down" size="size-3.5" class="me-1 text-stone-400" />
                    </button>
                </x-slot:trigger>

                <div class="border-b border-stone-100 px-4 py-3 dark:border-night-800">
                    <p class="truncate text-sm font-bold text-stone-900 dark:text-white">{{ auth()->user()->name }}</p>
                    <p class="truncate text-[11px] text-stone-500 dark:text-stone-400">{{ auth()->user()->email }}</p>
                </div>

                <div class="p-1.5">
                    <a href="{{ route('settings.index') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-stone-600 transition hover:bg-stone-50 dark:text-stone-300 dark:hover:bg-night-800">
                        <x-ui.icon name="user" size="size-4" />
                        حسابي والإعدادات
                    </a>
                    <a href="{{ route('notifications.index') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-stone-600 transition hover:bg-stone-50 dark:text-stone-300 dark:hover:bg-night-800">
                        <x-ui.icon name="bell" size="size-4" />
                        الإشعارات
                    </a>
                </div>

                <div class="border-t border-stone-100 p-1.5 dark:border-night-800">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10">
                            <x-ui.icon name="logout" size="size-4" />
                            تسجيل الخروج
                        </button>
                    </form>
                </div>
            </x-ui.dropdown>
        </div>
    </div>
</header>
