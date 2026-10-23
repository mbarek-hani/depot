<x-layouts.guest title="غير مصرح" heading="لا تملك صلاحية الوصول" description="ليست لديك صلاحية لعرض هذه الصفحة أو تنفيذ هذا الإجراء.">
    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 text-center dark:border-rose-500/20 dark:bg-rose-500/10">
        <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300">
            <x-ui.icon name="lock" size="size-6" />
        </span>
        <p class="mt-4 text-sm leading-7 text-rose-800 dark:text-rose-200">
            يمكنك العودة إلى الصفحات المتاحة لحسابك، أو التواصل مع مدير النظام إذا كنت تعتقد أن هذا خطأ.
        </p>
    </div>

    <div class="mt-5 flex flex-col gap-2 sm:flex-row">
        @auth
            <x-ui.button :href="route('dashboard')" icon="home" class="flex-1">العودة للوحة التحكم</x-ui.button>
        @else
            <x-ui.button :href="route('login')" icon="logout" class="flex-1">تسجيل الدخول</x-ui.button>
        @endauth
        <x-ui.button :href="url()->previous()" variant="outline" icon="arrow-right" class="flex-1">رجوع</x-ui.button>
    </div>
</x-layouts.guest>
