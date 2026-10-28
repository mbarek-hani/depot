<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl" data-theme-endpoint="{{ route('settings.theme') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#facc15">

    <title inertia>{{ config('app.name') }}</title>

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

    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="min-h-screen bg-cream-100 text-stone-900 dark:bg-night-950 dark:text-stone-100">
    @inertia
</body>
</html>
