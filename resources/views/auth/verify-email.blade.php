<x-layouts.guest title="تأكيد البريد الإلكتروني" heading="تأكيد البريد الإلكتروني" description="قبل المتابعة، يرجى تأكيد بريدك الإلكتروني من خلال الرابط الذي أرسلناه إليك.">
    @if ($errors->any())
        <x-ui.alert tone="danger" class="mb-4">{{ $errors->first() }}</x-ui.alert>
    @endif

    <div class="rounded-2xl border border-stone-200 bg-white p-6 text-center shadow-card dark:border-night-800 dark:bg-night-900">
        <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-brand-100 text-brand-800 dark:bg-brand-400/15 dark:text-brand-300">
            <x-ui.icon name="mail" size="size-7" />
        </span>
        <p class="mt-4 text-sm leading-6 text-stone-600 dark:text-stone-300">
            أرسلنا رابط التأكيد إلى
            <span class="font-bold text-stone-900 dark:text-white">{{ auth()->user()->email }}</span>.
        </p>

        <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
            @csrf
            <x-ui.button type="submit" class="w-full">
                إعادة إرسال رابط التأكيد
            </x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <x-ui.button type="submit" variant="ghost" class="w-full">
                تسجيل الخروج
            </x-ui.button>
        </form>
    </div>

    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-[11px] leading-5 text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200">
        <x-ui.icon name="info" size="size-4" class="mt-0.5 shrink-0" />
        <p>في بيئة التطوير المحلية، يظهر رابط التأكيد في ملف <span class="font-mono">storage/logs/laravel.log</span> وعند إنشاء حساب المدير من سطر الأوامر.</p>
    </div>
</x-layouts.guest>
