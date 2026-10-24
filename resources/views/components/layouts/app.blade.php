@props(['title' => 'لوحة التحكم'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl" data-theme-endpoint="{{ route('settings.theme') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#facc15">

    <title>{{ $title }} · {{ $associationName }}</title>

    <script>
        (function () {
            try {
                var stored = localStorage.getItem('theme');
                var server = @json(auth()->user()?->theme ?? 'system');
                var hasStored = ['light', 'dark', 'system'].indexOf(stored) !== -1;
                var choice = hasStored ? stored : server;
                var dark = choice === 'dark' || (choice === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

                document.documentElement.classList.toggle('dark', dark);
                document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
                document.documentElement.dataset.theme = choice;
            } catch (error) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream-100 text-stone-900 dark:bg-night-950 dark:text-stone-100">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-[100] focus:rounded-xl focus:bg-brand-400 focus:px-4 focus:py-2 focus:font-bold focus:text-night-950">
        الانتقال إلى المحتوى
    </a>

    <div x-data="{ sidebarOpen: false }">
        <x-layout.sidebar />

        <div class="min-h-screen lg:ms-72">
            <x-layout.topbar :latest-notifications="$latestNotifications" :unread-count="$unreadCount" />

            <main id="main-content" class="mx-auto w-full max-w-7xl px-4 pb-[calc(6rem+env(safe-area-inset-bottom))] pt-6 sm:px-6 lg:px-8 lg:pb-12 lg:pt-8">
                <div class="mb-5 space-y-3">
                    <x-ui.flash />
                </div>

                {{ $slot }}
            </main>
        </div>

        <x-layout.mobile-bottom-nav />
    </div>

    @stack('scripts')
</body>
</html>
