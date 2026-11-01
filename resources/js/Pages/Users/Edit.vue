<script setup>
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Components/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import UiButton from '../../Components/UiButton.vue';
import Field from '../../Components/Field.vue';
import Icon from '../../Components/Icon.vue';
import Badge from '../../Components/Badge.vue';

const props = defineProps({
    user: { type: Object, required: true },
});

const form = useForm({
    name: props.user.name,
    is_active: props.user.is_active,
    password: '',
    password_confirmation: '',
});

const inputClass = 'h-11 w-full rounded-xl border border-stone-300 bg-white px-3.5 text-sm text-stone-900 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-950 dark:text-white';

function submit() {
    form.patch(route('users.update', props.user.id), {
        preserveScroll: true,
        onSuccess: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <AppLayout :title="`تعديل ${user.name}`">
        <PageHeader :title="`تعديل: ${user.name}`" description="تحديث الاسم أو حالة الدخول أو كلمة المرور." eyebrow="الإدارة">
            <template #actions>
                <UiButton :href="route('users.index')" variant="outline" icon="arrow-right">عودة للمستخدمين</UiButton>
            </template>
        </PageHeader>

        <div class="mt-6 grid max-w-5xl gap-4 xl:grid-cols-3">
            <div class="xl:col-span-2">
                <div class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card dark:border-night-800 dark:bg-night-900">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-brand-100 text-brand-900 dark:bg-brand-400/15 dark:text-brand-300"><Icon name="user" :size="20" /></span>
                        <div>
                            <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">بيانات الحساب</h2>
                            <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">تعديل الاسم أو كلمة المرور أو إيقاف الدخول.</p>
                        </div>
                    </div>

                    <form class="space-y-4" @submit.prevent="submit">
                        <Field label="اسم المستخدم" :error="form.errors.name" required>
                            <input v-model="form.name" :class="inputClass">
                        </Field>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <Field label="كلمة مرور جديدة" :error="form.errors.password" hint="اتركها فارغة لعدم التغيير.">
                                <input v-model="form.password" type="password" :class="inputClass" autocomplete="new-password">
                            </Field>
                            <Field label="تأكيد كلمة المرور" :error="form.errors.password_confirmation">
                                <input v-model="form.password_confirmation" type="password" :class="inputClass" autocomplete="new-password">
                            </Field>
                        </div>

                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-stone-200 p-3.5 transition hover:border-stone-300 dark:border-night-800 dark:hover:border-stone-700">
                            <input v-model="form.is_active" type="checkbox" class="mt-0.5 size-4 rounded border-stone-300 text-brand-500 focus:ring-brand-400 dark:border-night-700 dark:bg-night-900">
                            <span>
                                <span class="block text-sm font-bold text-stone-800 dark:text-stone-100">الحساب نشط</span>
                                <span class="mt-0.5 block text-[11px] text-stone-500 dark:text-stone-400">عند الإيقاف لن يتمكن المستخدم من تسجيل الدخول، وستُغلق جلساته الحالية.</span>
                            </span>
                        </label>

                        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <UiButton :href="route('users.index')" variant="outline">إلغاء</UiButton>
                            <UiButton type="submit" icon="check" :disabled="form.processing">حفظ التعديلات</UiButton>
                        </div>
                    </form>
                </div>
            </div>

            <div class="h-fit rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card dark:border-night-800 dark:bg-night-900">
                <div class="flex flex-col items-center text-center">
                    <span class="flex size-16 items-center justify-center rounded-full bg-brand-100 font-display text-xl font-extrabold text-brand-800 dark:bg-brand-400/15 dark:text-brand-300">{{ user.name.slice(0, 1) }}</span>
                    <h3 class="mt-3 font-display text-base font-extrabold text-stone-900 dark:text-white">{{ user.name }}</h3>
                    <div class="mt-2"><Badge :tone="user.role_tone">{{ user.role_label }}</Badge></div>
                    <div class="mt-2"><Badge :tone="user.status_tone" dot>{{ user.status_label }}</Badge></div>
                </div>

                <dl class="mt-5 space-y-3 border-t border-stone-100 pt-4 text-xs dark:border-night-800">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-stone-400">آخر دخول</dt>
                        <dd class="font-semibold text-stone-700 dark:text-stone-200">{{ user.last_login_formatted }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-stone-400">عدد الحركات</dt>
                        <dd class="font-semibold text-stone-700 dark:text-stone-200">{{ user.movements_count }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-stone-400">أنشئ بواسطة</dt>
                        <dd class="font-semibold text-stone-700 dark:text-stone-200">{{ user.created_by_name ?? '—' }}</dd>
                    </div>
                </dl>

                <div class="mt-5 flex items-start gap-2.5 rounded-xl bg-amber-50 p-3.5 text-[11px] leading-5 text-amber-800 dark:bg-amber-500/10 dark:text-amber-200">
                    <Icon name="info" :size="16" class="mt-0.5 shrink-0" />
                    <p>عند تغيير كلمة المرور أو إيقاف الحساب سيتم تسجيل خروج المستخدم من جميع جلساته.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
