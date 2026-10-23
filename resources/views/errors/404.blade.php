<x-layouts.guest title="الصفحة غير موجودة" heading="لم نعثر على هذه الصفحة" description="قد يكون الرابط غير صحيح أو أن الصفحة تم نقلها أو حذفها.">
    <div class="rounded-2xl border border-stone-200 bg-white p-5 text-center dark:border-night-800 dark:bg-night-950">
        <p class="font-display text-5xl font-extrabold text-brand-500">404</p>
        <p class="mt-2 text-sm leading-7 text-stone-500 dark:text-stone-400">
            تأكد من الرابط، أو ابدأ من لوحة التحكم للوصول إلى المخزون والتقارير.
        </p>
    </div>

    <div class="mt-5 flex flex-col gap-2 sm:flex-row">
        @auth
            <x-ui.button :href="route('dashboard')" icon="home" class="flex-1">لوحة التحكم</x-ui.button>
        @else
            <x-ui.button :href="route('login')" icon="logout" class="flex-1">تسجيل الدخول</x-ui.button>
        @endauth
        <x-ui.button :href="url()->previous()" variant="outline" icon="arrow-right" class="flex-1">رجوع</x-ui.button>
    </div>
</x-layouts.guest>
