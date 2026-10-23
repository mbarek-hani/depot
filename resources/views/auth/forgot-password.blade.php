<x-layouts.guest title="نسيت كلمة المرور" heading="استعادة كلمة المرور" description="أدخل بريدك الإلكتروني وسنرسل لك رابطًا لإعادة تعيين كلمة المرور.">
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
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

        <x-ui.button type="submit" size="lg" class="w-full">
            إرسال رابط الاستعادة
        </x-ui.button>
    </form>

    <p class="mt-6 text-center text-xs text-stone-500 dark:text-stone-400">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1 font-bold text-brand-700 hover:underline dark:text-brand-300">
            <x-ui.icon name="arrow-right" size="size-3.5" />
            العودة لتسجيل الدخول
        </a>
    </p>
</x-layouts.guest>
