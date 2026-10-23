<x-layouts.guest title="انتهت الجلسة" heading="انتهت صلاحية الجلسة" description="لأسباب أمنية، انتهت جلسة العمل الحالية. يرجى تحديث الصفحة والمحاولة مرة أخرى.">
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-center dark:border-amber-500/20 dark:bg-amber-500/10">
        <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300">
            <x-ui.icon name="clock" size="size-6" />
        </span>
        <p class="mt-4 text-sm leading-7 text-amber-800 dark:text-amber-200">
            أعد تحميل الصفحة ثم قم بالمحاولة مجددًا. إذا استمرت المشكلة، سجّل الخروج ثم ادخل من جديد.
        </p>
    </div>

    <div class="mt-5 flex flex-col gap-2 sm:flex-row">
        <x-ui.button :href="url()->current()" icon="refresh" class="flex-1">تحديث الصفحة</x-ui.button>
        <x-ui.button :href="route('dashboard')" variant="outline" icon="home" class="flex-1">لوحة التحكم</x-ui.button>
    </div>
</x-layouts.guest>
