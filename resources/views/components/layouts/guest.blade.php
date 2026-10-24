@props(['title' => 'تسجيل الدخول', 'heading' => null, 'description' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
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
                var choice = ['light', 'dark', 'system'].indexOf(stored) !== -1 ? stored : 'system';
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
    <div class="relative min-h-screen lg:grid lg:grid-cols-[1.05fr_1fr]">
        <div class="absolute end-4 top-4 z-20">
            <x-ui.theme-toggle />
        </div>

        <section class="relative hidden overflow-hidden bg-night-900 p-10 text-white lg:flex lg:flex-col xl:p-14">
            <div class="pointer-events-none absolute -end-32 -top-32 size-[28rem] rounded-full bg-brand-400/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-40 -start-24 size-[26rem] rounded-full bg-maroon-600/25 blur-3xl"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 22px 22px;"></div>

            <div class="relative z-10">
                <x-ui.logo size="xl" with-text text-class="text-white" />
            </div>

            <div class="relative z-10 my-auto max-w-lg py-12">
                <span class="inline-flex items-center gap-2 rounded-full border border-brand-400/30 bg-brand-400/10 px-3 py-1 text-[11px] font-bold text-brand-200">
                    <x-ui.icon name="sparkles" size="size-3.5" />
                    نظام إدارة المخزون
                </span>

                <h1 class="mt-6 font-display text-4xl font-extrabold leading-[1.25] text-white xl:text-5xl">
                    كل ما تحتاجه لإدارة
                    <span class="text-brand-400">مخزونك</span>
                    في مكان واحد
                </h1>

                <p class="mt-4 max-w-md text-sm leading-7 text-stone-400">
                    تابع الكميات، راقب تواريخ الصلاحية، واستخرج التقارير بسهولة وبكل احترافية.
                </p>

                <ul class="mt-9 space-y-4">
                    @foreach ([
                        ['archive', 'متابعة دقيقة للمخزون والحركات'],
                        ['calendar', 'تنبيهات ذكية لقرب انتهاء الصلاحية'],
                        ['chart-bar', 'تقارير PDF شاملة وجاهزة للطباعة'],
                    ] as [$icon, $label])
                        <li class="flex items-center gap-3 text-sm font-medium text-stone-300">
                            <span class="flex size-9 items-center justify-center rounded-xl bg-white/5 text-brand-400 ring-1 ring-white/10">
                                <x-ui.icon :name="$icon" size="size-4.5" />
                            </span>
                            {{ $label }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="relative z-10 flex items-center justify-between border-t border-white/10 pt-5 text-[11px] text-stone-500">
                <span>{{ $associationName }}</span>
                <span>إدارة المخزون</span>
            </div>
        </section>

        <section class="flex min-h-screen flex-col justify-center px-4 py-14 sm:px-8 lg:px-12 xl:px-20">
            <div class="mx-auto w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    <x-ui.logo size="lg" with-text text-class="text-stone-900 dark:text-white" />
                </div>

                <div class="mb-7">
                    <h2 class="font-display text-2xl font-extrabold text-stone-900 dark:text-white sm:text-3xl">{{ $heading ?? $title }}</h2>
                    @if ($description)
                        <p class="mt-2 text-sm leading-6 text-stone-500 dark:text-stone-400">{{ $description }}</p>
                    @endif
                </div>

                <div class="mb-5 space-y-3">
                    <x-ui.flash />
                </div>

                {{ $slot }}

                <p class="mt-10 text-center text-[11px] leading-5 text-stone-400 dark:text-stone-500">
                    {{ $associationName }} · جميع الحقوق محفوظة {{ now()->year }}
                </p>
            </div>
        </section>
    </div>
</body>
</html>
