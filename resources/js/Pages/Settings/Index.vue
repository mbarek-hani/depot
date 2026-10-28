<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Components/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import UiButton from '../../Components/UiButton.vue';
import Field from '../../Components/Field.vue';
import Icon from '../../Components/Icon.vue';
import Badge from '../../Components/Badge.vue';

const props = defineProps({
    tab: { type: String, default: 'general' },
    settings: { type: Object, required: true },
});

const page = usePage();
const user = page.props.auth?.user;

const tabs = [
    { key: 'general', label: 'إعدادات النظام', icon: 'settings' },
    { key: 'appearance', label: 'المظهر', icon: 'sparkles' },
    { key: 'account', label: 'حسابي', icon: 'user' },
];

const inputClass = 'h-11 w-full rounded-xl border border-stone-300 bg-white px-3.5 text-sm text-stone-900 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-950 dark:text-white';
const textareaClass = 'min-h-20 w-full rounded-xl border border-stone-300 bg-white px-3.5 py-3 text-sm text-stone-900 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-950 dark:text-white';

const general = useForm({
    association_name: props.settings.association_name ?? '',
    association_phone: props.settings.association_phone ?? '',
    association_email: props.settings.association_email ?? '',
    low_stock_threshold: props.settings.low_stock_threshold ?? '',
    expiry_warning_days: props.settings.expiry_warning_days ?? '',
    currency: props.settings.currency ?? 'MAD',
    report_footer: props.settings.report_footer ?? '',
});

const account = useForm({
    name: user?.name ?? '',
});

const password = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const currentTheme = ref('system');
const themeOptions = [
    { value: 'light', label: 'فاتح', description: 'واجهة نهارية مريحة', icon: 'sun' },
    { value: 'dark', label: 'داكن', description: 'واجهة ليلية هادئة', icon: 'moon' },
    { value: 'system', label: 'النظام', description: 'يتبع إعداد جهازك', icon: 'monitor' },
];

function syncTheme(event) {
    currentTheme.value = event?.detail?.theme ?? document.documentElement.dataset.theme ?? 'system';
}

function chooseTheme(theme) {
    currentTheme.value = theme;
    window.themeManager?.set(theme);
}

function saveGeneral() {
    general.patch(route('settings.general'), { preserveScroll: true });
}

function saveAccount() {
    account.patch(route('settings.account'), { preserveScroll: true });
}

function savePassword() {
    password.patch(route('settings.password'), {
        preserveScroll: true,
        onSuccess: () => password.reset(),
    });
}

onMounted(() => {
    syncTheme();
    window.addEventListener('theme-changed', syncTheme);
});

onBeforeUnmount(() => window.removeEventListener('theme-changed', syncTheme));
</script>

<template>
    <AppLayout title="الإعدادات">
        <PageHeader title="الإعدادات" description="خصّص بيانات الجمعية وحدود التنبيهات ومظهر النظام وحسابك الشخصي." eyebrow="الإعدادات" />

        <div class="mt-6 space-y-4">
            <div class="flex gap-1.5 overflow-x-auto rounded-2xl border border-stone-200/80 bg-white p-1.5 shadow-card scrollbar-thin sm:gap-2 dark:border-night-800 dark:bg-night-900">
                <Link
                    v-for="item in tabs"
                    :key="item.key"
                    :href="route('settings.index', { tab: item.key })"
                    class="flex flex-1 items-center justify-center gap-1.5 whitespace-nowrap rounded-xl px-3 py-2.5 text-[11px] font-bold transition sm:gap-2 sm:px-4 sm:text-xs"
                    :class="tab === item.key ? 'bg-brand-400 text-night-950 shadow-sm' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-800 dark:text-stone-400 dark:hover:bg-night-800 dark:hover:text-white'"
                >
                    <Icon :name="item.icon" :size="16" />
                    {{ item.label }}
                </Link>
            </div>

            <template v-if="tab === 'general'">
                <div class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card dark:border-night-800 dark:bg-night-900">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-brand-100 text-brand-900 dark:bg-brand-400/15 dark:text-brand-300"><Icon name="store" :size="20" /></span>
                        <div>
                            <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">بيانات الجمعية</h2>
                            <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">تظهر هذه البيانات في رأس التقارير المطبوعة.</p>
                        </div>
                    </div>

                    <form class="space-y-4" @submit.prevent="saveGeneral">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <Field label="اسم الجمعية" :error="general.errors.association_name" required>
                                <input v-model="general.association_name" :class="inputClass">
                            </Field>
                            <Field label="رقم التواصل" :error="general.errors.association_phone">
                                <input v-model="general.association_phone" :class="inputClass" placeholder="05xxxxxxxx" dir="ltr">
                            </Field>
                            <Field label="البريد الإلكتروني" :error="general.errors.association_email">
                                <input v-model="general.association_email" type="email" :class="inputClass" placeholder="info@example.com" dir="ltr">
                            </Field>
                            <Field label="العملة" :error="general.errors.currency" hint="تظهر بجانب القيم المالية في التقارير." required>
                                <input v-model="general.currency" :class="inputClass" dir="ltr">
                            </Field>
                            <Field label="حد المخزون الافتراضي" :error="general.errors.low_stock_threshold" hint="يمكن تجاوزه لكل منتج على حدة." required>
                                <input v-model="general.low_stock_threshold" type="number" step="1" min="0" :class="inputClass">
                            </Field>
                            <Field label="أيام التنبيه قبل انتهاء الصلاحية" :error="general.errors.expiry_warning_days" required>
                                <input v-model="general.expiry_warning_days" type="number" step="1" min="1" :class="inputClass">
                            </Field>
                        </div>

                        <Field label="تذييل التقارير" :error="general.errors.report_footer" hint="نص قصير يظهر أسفل كل تقرير PDF.">
                            <textarea v-model="general.report_footer" :class="textareaClass" rows="2"></textarea>
                        </Field>

                        <div class="flex justify-end">
                            <UiButton type="submit" icon="check" :disabled="general.processing">حفظ الإعدادات</UiButton>
                        </div>
                    </form>
                </div>

                <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-[11px] leading-6 text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200">
                    <Icon name="shield" :size="18" class="mt-0.5 shrink-0" />
                    <p>تُنشأ الحسابات محليًا عبر الأمر <span class="rounded bg-white/70 px-1.5 py-0.5 font-mono dark:bg-night-950/50">php artisan user:create</span>، ويمكن تغيير كلمة المرور عبر الأمر <span class="rounded bg-white/70 px-1.5 py-0.5 font-mono dark:bg-night-950/50">php artisan user:password</span>.</p>
                </div>
            </template>

            <template v-else-if="tab === 'appearance'">
                <div class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card dark:border-night-800 dark:bg-night-900">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-brand-100 text-brand-900 dark:bg-brand-400/15 dark:text-brand-300"><Icon name="sparkles" :size="20" /></span>
                        <div>
                            <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">مظهر النظام</h2>
                            <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">اختر الوضع المناسب لك. يُحفظ اختيارك تلقائيًا.</p>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <button
                            v-for="option in themeOptions"
                            :key="option.value"
                            type="button"
                            class="flex flex-col items-start gap-3 rounded-2xl border p-4 text-start transition"
                            :class="currentTheme === option.value ? 'border-brand-400 bg-brand-50 ring-2 ring-brand-400/30 dark:border-brand-400 dark:bg-brand-400/10' : 'border-stone-200 bg-white hover:border-stone-300 dark:border-night-800 dark:bg-night-950 dark:hover:border-stone-700'"
                            @click="chooseTheme(option.value)"
                        >
                            <span class="flex size-10 items-center justify-center rounded-xl" :class="currentTheme === option.value ? 'bg-brand-400 text-night-950' : 'bg-stone-100 text-stone-500 dark:bg-night-800 dark:text-stone-300'">
                                <Icon :name="option.icon" :size="20" />
                            </span>
                            <span>
                                <span class="block text-sm font-bold text-stone-900 dark:text-white">{{ option.label }}</span>
                                <span class="mt-0.5 block text-[11px] text-stone-500 dark:text-stone-400">{{ option.description }}</span>
                            </span>
                        </button>
                    </div>

                    <div class="mt-5 flex items-start gap-2.5 rounded-xl bg-stone-50 p-4 text-[11px] leading-6 text-stone-500 dark:bg-night-950/60 dark:text-stone-400">
                        <Icon name="info" :size="16" class="mt-0.5 shrink-0 text-brand-600 dark:text-brand-300" />
                        <p>وضع «النظام» يتبع إعداد المظهر في جهازك تلقائيًا. يمكنك أيضًا تبديل المظهر بسرعة من زر الشمس/القمر في الشريط العلوي.</p>
                    </div>
                </div>
            </template>

            <template v-else>
                <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                    <div class="space-y-4 xl:col-span-2">
                        <div class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card dark:border-night-800 dark:bg-night-900">
                            <div class="mb-5 flex items-center gap-3">
                                <span class="flex size-10 items-center justify-center rounded-xl bg-brand-100 text-brand-900 dark:bg-brand-400/15 dark:text-brand-300"><Icon name="user" :size="20" /></span>
                                <div>
                                    <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">بيانات الحساب</h2>
                                    <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">حدّث اسم الحساب المحلي.</p>
                                </div>
                            </div>

                            <form class="space-y-4" @submit.prevent="saveAccount">
                                <Field label="اسم الحساب" :error="account.errors.name" required>
                                    <input v-model="account.name" :class="inputClass">
                                </Field>
                                <div class="flex justify-end"><UiButton type="submit" icon="check" :disabled="account.processing">حفظ البيانات</UiButton></div>
                            </form>
                        </div>

                        <div class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card dark:border-night-800 dark:bg-night-900">
                            <div class="mb-5 flex items-center gap-3">
                                <span class="flex size-10 items-center justify-center rounded-xl bg-stone-100 text-stone-600 dark:bg-night-800 dark:text-stone-300"><Icon name="key" :size="20" /></span>
                                <div>
                                    <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">تغيير كلمة المرور</h2>
                                    <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">استخدم كلمة مرور قوية لا تقل عن 8 أحرف وتتضمن حرفًا ورقمًا.</p>
                                </div>
                            </div>

                            <form class="space-y-4" @submit.prevent="savePassword">
                                <Field label="كلمة المرور الحالية" :error="password.errors.current_password" required>
                                    <input v-model="password.current_password" type="password" :class="inputClass" autocomplete="current-password">
                                </Field>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <Field label="كلمة المرور الجديدة" :error="password.errors.password" required>
                                        <input v-model="password.password" type="password" :class="inputClass" autocomplete="new-password">
                                    </Field>
                                    <Field label="تأكيد كلمة المرور" required>
                                        <input v-model="password.password_confirmation" type="password" :class="inputClass" autocomplete="new-password">
                                    </Field>
                                </div>
                                <div class="flex justify-end"><UiButton type="submit" variant="secondary" icon="lock" :disabled="password.processing">تحديث كلمة المرور</UiButton></div>
                            </form>
                        </div>
                    </div>

                    <div class="h-fit rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card dark:border-night-800 dark:bg-night-900">
                        <div class="flex flex-col items-center text-center">
                            <span class="flex size-16 items-center justify-center rounded-full bg-brand-100 font-display text-xl font-extrabold text-brand-800 dark:bg-brand-400/15 dark:text-brand-300">{{ user?.name?.slice(0, 1) }}</span>
                            <h3 class="mt-3 font-display text-base font-extrabold text-stone-900 dark:text-white">{{ user?.name }}</h3>
                            <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">حساب محلي</p>
                            <div class="mt-3"><Badge tone="brand">صلاحية كاملة</Badge></div>
                        </div>

                        <dl class="mt-5 space-y-3 border-t border-stone-100 pt-4 text-xs dark:border-night-800">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-stone-400">تاريخ الانضمام</dt>
                                <dd class="font-semibold text-stone-700 dark:text-stone-200">{{ user?.created_at ?? '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-stone-400">آخر تحديث</dt>
                                <dd class="font-semibold text-stone-700 dark:text-stone-200">{{ user?.updated_human ?? '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-stone-400">الصلاحية</dt>
                                <dd class="font-semibold text-stone-700 dark:text-stone-200">صلاحية كاملة</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
