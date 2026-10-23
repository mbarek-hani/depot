@props([
    'user' => null,
    'action',
    'method' => 'POST',
    'submitLabel' => 'حفظ',
])

<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <div class="mb-5 flex items-center gap-3">
            <span class="flex size-10 items-center justify-center rounded-xl bg-brand-100 text-brand-800 dark:bg-brand-400/15 dark:text-brand-300">
                <x-ui.icon name="user" size="size-5" />
            </span>
            <div>
                <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">بيانات الحساب</h2>
                <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">الاسم والبريد الإلكتروني وكلمة المرور.</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-ui.input name="name" label="الاسم" :value="$user?->name" placeholder="الاسم الكامل" required />
            <x-ui.input name="email" label="البريد الإلكتروني" type="email" :value="$user?->email" placeholder="name@example.com" required />

            <x-ui.input
                name="password"
                label="كلمة المرور"
                type="password"
                :required="$user === null"
                :hint="$user ? 'اتركها فارغة لعدم التغيير.' : '8 أحرف على الأقل وتتضمن حرفًا ورقمًا.'"
                autocomplete="new-password"
            />
            <x-ui.input name="password_confirmation" label="تأكيد كلمة المرور" type="password" :required="$user === null" autocomplete="new-password" />

            @if ($user)
                <div class="sm:col-span-2">
                    <x-ui.toggle name="is_active" label="الحساب نشط" description="عند الإيقاف لن يتمكن المستخدم من تسجيل الدخول." :checked="$user->is_active" />
                </div>
            @endif
        </div>

        @if ($user?->isAdmin())
            <div class="mt-4 flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-[11px] leading-5 text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200">
                <x-ui.icon name="info" size="size-4" class="mt-0.5 shrink-0" />
                <p>عند تغيير بريد حساب مدير النظام، سيُطلب تأكيد البريد الجديد قبل المتابعة.</p>
            </div>
        @endif
    </x-ui.card>

    <x-ui.card>
        <div class="mb-5 flex items-center gap-3">
            <span class="flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300">
                <x-ui.icon name="bell" size="size-5" />
            </span>
            <div>
                <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">تنبيهات المستخدم</h2>
                <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">اختر التنبيهات التي سيستلمها هذا المستخدم داخل النظام.</p>
            </div>
        </div>

        <div class="space-y-3">
            <x-ui.toggle
                name="receive_low_stock_alerts"
                label="تنبيهات المخزون المنخفض"
                description="إشعار عند وصول المنتجات إلى الحد الأدنى أو نفادها."
                :checked="$user?->receive_low_stock_alerts"
            />
            <x-ui.toggle
                name="receive_expiry_alerts"
                label="تنبيهات قرب انتهاء الصلاحية"
                description="إشعار عند اقتراب تاريخ انتهاء المنتجات."
                :checked="$user?->receive_expiry_alerts"
            />
        </div>
    </x-ui.card>

    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
        <x-ui.button :href="route('users.index')" variant="outline">إلغاء</x-ui.button>
        <x-ui.button type="submit" icon="check">{{ $submitLabel }}</x-ui.button>
    </div>
</form>
