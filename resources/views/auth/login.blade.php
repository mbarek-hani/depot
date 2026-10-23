<x-layouts.guest title="تسجيل الدخول" heading="مرحبًا بعودتك" description="سجّل الدخول للمتابعة إلى لوحة إدارة المخزون.">
    <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
        @csrf

        <x-ui.input
            name="email"
            label="البريد الإلكتروني"
            type="email"
            placeholder="name@example.com"
            icon="mail"
            required
            autofocus
            autocomplete="username"
        />

        <div class="space-y-1.5">
            <div class="flex items-center justify-between gap-3">
                <label for="field-password" class="text-sm font-semibold text-stone-700 dark:text-stone-200">كلمة المرور</label>
                <a href="{{ route('password.request') }}" class="text-xs font-bold text-brand-700 hover:underline dark:text-brand-300">نسيت كلمة المرور؟</a>
            </div>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-stone-400">
                    <x-ui.icon name="lock" size="size-4.5" />
                </span>
                <input
                    type="password"
                    name="password"
                    id="field-password"
                    required
                    autocomplete="current-password"
                    class="h-11 w-full rounded-xl border border-stone-300 bg-white px-3.5 ps-10 text-sm text-stone-900 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-950 dark:text-white"
                    placeholder="••••••••"
                >
            </div>
            @error('password')
                <p class="text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-medium text-stone-600 dark:text-stone-300">
            <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="size-4 rounded border-stone-300 text-brand-500 focus:ring-brand-400 dark:border-night-700 dark:bg-night-900">
            تذكرني على هذا الجهاز
        </label>

        <x-ui.button type="submit" size="lg" class="w-full">
            تسجيل الدخول
            <x-ui.icon name="arrow-left" size="size-4.5" />
        </x-ui.button>
    </form>

    <div class="mt-6 flex items-start gap-2.5 rounded-xl border border-stone-200 bg-white/60 p-3.5 text-[11px] leading-5 text-stone-500 dark:border-night-800 dark:bg-night-900/50 dark:text-stone-400">
        <x-ui.icon name="info" size="size-4" class="mt-0.5 shrink-0 text-brand-600 dark:text-brand-300" />
        <p>لا يوجد تسجيل عام. تُنشأ الحسابات من قبل مدير النظام فقط.</p>
    </div>
</x-layouts.guest>
