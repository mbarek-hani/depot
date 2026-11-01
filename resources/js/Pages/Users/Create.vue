<script setup>
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Components/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import UiButton from '../../Components/UiButton.vue';
import Field from '../../Components/Field.vue';
import Icon from '../../Components/Icon.vue';

const form = useForm({
    name: '',
    password: '',
    password_confirmation: '',
});

const inputClass = 'h-11 w-full rounded-xl border border-stone-300 bg-white px-3.5 text-sm text-stone-900 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-950 dark:text-white';

function submit() {
    form.post(route('users.store'));
}
</script>

<template>
    <AppLayout title="إضافة مستخدم">
        <PageHeader title="إضافة مستخدم جديد" description="أنشئ حساب مستخدم يمكنه قراءة البيانات وتنزيل التقارير." eyebrow="الإدارة">
            <template #actions>
                <UiButton :href="route('users.index')" variant="outline" icon="arrow-right">عودة للمستخدمين</UiButton>
            </template>
        </PageHeader>

        <div class="mt-6 max-w-3xl">
            <div class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card dark:border-night-800 dark:bg-night-900">
                <div class="mb-5 flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-brand-100 text-brand-900 dark:bg-brand-400/15 dark:text-brand-300"><Icon name="user" :size="20" /></span>
                    <div>
                        <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">بيانات الحساب</h2>
                        <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">اسم المستخدم وكلمة المرور فقط.</p>
                    </div>
                </div>

                <form class="space-y-4" @submit.prevent="submit">
                    <Field label="اسم المستخدم" :error="form.errors.name" required>
                        <input v-model="form.name" :class="inputClass" placeholder="الاسم الكامل" autocomplete="off">
                    </Field>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <Field label="كلمة المرور" :error="form.errors.password" hint="8 أحرف على الأقل وتتضمن حرفًا ورقمًا." required>
                            <input v-model="form.password" type="password" :class="inputClass" autocomplete="new-password">
                        </Field>
                        <Field label="تأكيد كلمة المرور" required>
                            <input v-model="form.password_confirmation" type="password" :class="inputClass" autocomplete="new-password">
                        </Field>
                    </div>

                    <div class="flex items-start gap-2.5 rounded-xl bg-stone-50 p-3.5 text-[11px] leading-5 text-stone-500 dark:bg-night-950/60 dark:text-stone-400">
                        <Icon name="info" :size="16" class="mt-0.5 shrink-0 text-brand-600 dark:text-brand-300" />
                        <p>سيُمنح الحساب صلاحية القراءة وتنزيل التقارير فقط. يستطيع المستخدم تغيير كلمة مروره من صفحة الإعدادات، وإذا نسيها يطلب منك إعادة تعيينها من هذه الصفحة.</p>
                    </div>

                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <UiButton :href="route('users.index')" variant="outline">إلغاء</UiButton>
                        <UiButton type="submit" icon="check" :disabled="form.processing">إنشاء الحساب</UiButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
