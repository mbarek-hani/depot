@php
    $user = auth()->user();

    $tabs = collect([
        'general' => ['label' => 'إعدادات النظام', 'icon' => 'settings', 'visible' => $user->isAdmin()],
        'appearance' => ['label' => 'المظهر', 'icon' => 'sparkles', 'visible' => true],
        'account' => ['label' => 'حسابي', 'icon' => 'user', 'visible' => true],
    ])->filter(fn ($tab) => $tab['visible']);
@endphp

<x-layouts.app title="الإعدادات">
    <x-layout.page-header
        title="الإعدادات"
        description="خصّص بيانات الجمعية وحدود التنبيهات ومظهر النظام وحسابك الشخصي."
        eyebrow="الإعدادات"
    />

    <div class="mt-6 space-y-4">
        <div class="flex gap-2 overflow-x-auto rounded-2xl border border-stone-200/80 bg-white p-1.5 shadow-card dark:border-night-800 dark:bg-night-900">
            @foreach ($tabs as $key => $item)
                <a
                    href="{{ route('settings.index', ['tab' => $key]) }}"
                    @class([
                        'flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-xl px-4 py-2.5 text-xs font-bold transition',
                        'bg-brand-400 text-night-950 shadow-sm' => $tab === $key,
                        'text-stone-500 hover:bg-stone-50 hover:text-stone-800 dark:text-stone-400 dark:hover:bg-night-800 dark:hover:text-white' => $tab !== $key,
                    ])
                >
                    <x-ui.icon :name="$item['icon']" size="size-4" />
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>

        @if ($tab === 'general')
            <x-ui.card>
                <div class="mb-5 flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-brand-100 text-brand-900 dark:bg-brand-400/15 dark:text-brand-300">
                        <x-ui.icon name="store" size="size-5" />
                    </span>
                    <div>
                        <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">بيانات الجمعية</h2>
                        <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">تظهر هذه البيانات في رأس التقارير المطبوعة.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.general') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.input name="association_name" label="اسم الجمعية" :value="$settings['association_name']" required />
                        <x-ui.input name="association_phone" label="رقم التواصل" :value="$settings['association_phone']" placeholder="05xxxxxxxx" />
                        <x-ui.input name="association_email" type="email" label="البريد الإلكتروني" :value="$settings['association_email']" placeholder="info@example.com" />
                        <x-ui.input name="currency" label="العملة" :value="$settings['currency']" hint="تظهر بجانب القيم المالية في التقارير." required />
                        <x-ui.input name="low_stock_threshold" type="number" step="1" min="0" label="حد المخزون الافتراضي" :value="$settings['low_stock_threshold']" hint="يمكن تجاوزه لكل منتج على حدة." required />
                        <x-ui.input name="expiry_warning_days" type="number" step="1" min="1" label="أيام التنبيه قبل انتهاء الصلاحية" :value="$settings['expiry_warning_days']" required />
                    </div>

                    <x-ui.textarea name="report_footer" label="تذييل التقارير" :value="$settings['report_footer']" :rows="2" hint="نص قصير يظهر أسفل كل تقرير PDF." />

                    <div class="flex justify-end">
                        <x-ui.button type="submit" icon="check">حفظ الإعدادات</x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-[11px] leading-6 text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200">
                <x-ui.icon name="shield" size="size-4.5" class="mt-0.5 shrink-0" />
                <p>حسابات مديري النظام تُنشأ فقط عبر الأمر <span class="rounded bg-white/70 px-1.5 py-0.5 font-mono dark:bg-night-950/50">php artisan admin:create</span>، بينما تُدار حسابات المستخدمين من صفحة المستخدمين.</p>
            </div>
        @endif

        @if ($tab === 'appearance')
            <x-ui.card>
                <div class="mb-5 flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-brand-100 text-brand-900 dark:bg-brand-400/15 dark:text-brand-300">
                        <x-ui.icon name="sparkles" size="size-5" />
                    </span>
                    <div>
                        <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">مظهر النظام</h2>
                        <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">اختر الوضع المناسب لك. يُحفظ اختيارك تلقائيًا.</p>
                    </div>
                </div>

                <x-settings.theme-selector />

                <div class="mt-5 flex items-start gap-2.5 rounded-xl bg-stone-50 p-4 text-[11px] leading-6 text-stone-500 dark:bg-night-950/60 dark:text-stone-400">
                    <x-ui.icon name="info" size="size-4" class="mt-0.5 shrink-0 text-brand-600 dark:text-brand-300" />
                    <p>وضع «النظام» يتبع إعداد المظهر في جهازك تلقائيًا. يمكنك أيضًا تبديل المظهر بسرعة من زر الشمس/القمر في الشريط العلوي.</p>
                </div>
            </x-ui.card>
        @endif

        @if ($tab === 'account')
            <div class="grid gap-4 xl:grid-cols-3">
                <div class="space-y-4 xl:col-span-2">
                    <x-ui.card>
                        <div class="mb-5 flex items-center gap-3">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-brand-100 text-brand-900 dark:bg-brand-400/15 dark:text-brand-300">
                                <x-ui.icon name="user" size="size-5" />
                            </span>
                            <div>
                                <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">بيانات الحساب</h2>
                                <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">حدّث اسمك وبريدك الإلكتروني.</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('settings.account') }}" class="space-y-4">
                            @csrf
                            @method('PATCH')
                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-ui.input name="name" label="الاسم" :value="$user->name" required />
                                <x-ui.input name="email" type="email" label="البريد الإلكتروني" :value="$user->email" required />
                            </div>

                            @if ($user->isAdmin())
                                <p class="text-[11px] leading-5 text-stone-400">عند تغيير البريد الإلكتروني سيُطلب تأكيد العنوان الجديد قبل متابعة استخدام النظام.</p>
                            @endif

                            <div class="flex justify-end">
                                <x-ui.button type="submit" icon="check">حفظ البيانات</x-ui.button>
                            </div>
                        </form>
                    </x-ui.card>

                    <x-ui.card>
                        <div class="mb-5 flex items-center gap-3">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-stone-100 text-stone-600 dark:bg-night-800 dark:text-stone-300">
                                <x-ui.icon name="key" size="size-5" />
                            </span>
                            <div>
                                <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">تغيير كلمة المرور</h2>
                                <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">استخدم كلمة مرور قوية لا تقل عن 8 أحرف وتتضمن حرفًا ورقمًا.</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('settings.password') }}" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            <x-ui.input name="current_password" type="password" label="كلمة المرور الحالية" autocomplete="current-password" required />

                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-ui.input name="password" type="password" label="كلمة المرور الجديدة" autocomplete="new-password" required />
                                <x-ui.input name="password_confirmation" type="password" label="تأكيد كلمة المرور" autocomplete="new-password" required />
                            </div>

                            <div class="flex justify-end">
                                <x-ui.button type="submit" variant="secondary" icon="lock">تحديث كلمة المرور</x-ui.button>
                            </div>
                        </form>
                    </x-ui.card>
                </div>

                <x-ui.card class="h-fit">
                    <div class="flex flex-col items-center text-center">
                        <x-ui.avatar :name="$user->name" size="size-16" />
                        <h3 class="mt-3 font-display text-base font-extrabold text-stone-900 dark:text-white">{{ $user->name }}</h3>
                        <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">{{ $user->email }}</p>
                        <div class="mt-3 flex flex-wrap justify-center gap-1.5">
                            <x-ui.badge :tone="$user->role->tone()">{{ $user->role->label() }}</x-ui.badge>
                            <x-ui.badge :tone="$user->is_active ? 'success' : 'danger'" dot>{{ $user->is_active ? 'نشط' : 'موقوف' }}</x-ui.badge>
                        </div>
                    </div>

                    <dl class="mt-5 space-y-3 border-t border-stone-100 pt-4 text-xs dark:border-night-800">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-stone-400">تاريخ الانضمام</dt>
                            <dd class="font-semibold text-stone-700 dark:text-stone-200">{{ $user->created_at?->translatedFormat('j F Y') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-stone-400">آخر تحديث</dt>
                            <dd class="font-semibold text-stone-700 dark:text-stone-200">{{ $user->updated_at?->diffForHumans() }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-stone-400">الصلاحية</dt>
                            <dd class="font-semibold text-stone-700 dark:text-stone-200">{{ $user->isAdmin() ? 'إدارة كاملة' : 'عرض وتقارير فقط' }}</dd>
                        </div>
                    </dl>
                </x-ui.card>
            </div>
        @endif
    </div>
</x-layouts.app>
