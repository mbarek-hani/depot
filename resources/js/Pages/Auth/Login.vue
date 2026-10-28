<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import Icon from '../../Components/Icon.vue';
import Logo from '../../Components/Logo.vue';
import ThemeToggle from '../../Components/ThemeToggle.vue';
import UiButton from '../../Components/UiButton.vue';

const page = usePage();
const app = page.props.app ?? {};

const form = useForm({
    name: '',
    password: '',
    remember: false,
});

function submit() {
    form.post(route('login.store'), {
        onFinish: () => form.reset('password'),
    });
}

const inputClass = 'h-11 w-full rounded-xl border border-stone-300 bg-white px-3.5 text-sm text-stone-900 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-950 dark:text-white';
</script>

<template>
    <Head title="تسجيل الدخول" />

    <div class="flex min-h-screen items-center justify-center bg-cream-100 px-4 py-10 dark:bg-night-950">
        <div class="fixed left-4 top-4 z-20">
            <ThemeToggle />
        </div>

        <div class="w-full max-w-md">
            <div class="mb-6 flex flex-col items-center text-center">
                <Logo size="lg" />
                <h1 class="mt-4 font-display text-2xl font-extrabold text-stone-900 dark:text-white">{{ app.name }}</h1>
                <p class="mt-1 text-xs font-semibold text-stone-400">نظام إدارة المخزون</p>
            </div>

            <div class="rounded-3xl border border-stone-200/80 bg-white p-6 shadow-pop dark:border-night-800 dark:bg-night-900 sm:p-8">
                <div class="mb-6">
                    <h2 class="font-display text-xl font-extrabold text-stone-900 dark:text-white">مرحبًا بعودتك</h2>
                    <p class="mt-1 text-xs leading-6 text-stone-500 dark:text-stone-400">سجّل الدخول للمتابعة إلى لوحة إدارة المخزون.</p>
                </div>

                <form class="space-y-5" @submit.prevent="submit">
                    <label class="block space-y-1.5">
                        <span class="block text-sm font-semibold text-stone-700 dark:text-stone-200">اسم الحساب</span>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-stone-400">
                                <Icon name="user" :size="18" />
                            </span>
                            <input v-model="form.name" :class="inputClass" class="ps-10" placeholder="أدخل اسم الحساب" autocomplete="username" autofocus>
                        </div>
                        <span v-if="form.errors.name" class="block text-[11px] font-medium text-rose-600">{{ form.errors.name }}</span>
                    </label>

                    <label class="block space-y-1.5">
                        <span class="block text-sm font-semibold text-stone-700 dark:text-stone-200">كلمة المرور</span>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-stone-400">
                                <Icon name="lock" :size="18" />
                            </span>
                            <input v-model="form.password" type="password" :class="inputClass" class="ps-10" placeholder="••••••••" autocomplete="current-password">
                        </div>
                        <span v-if="form.errors.password" class="block text-[11px] font-medium text-rose-600">{{ form.errors.password }}</span>
                    </label>

                    <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-medium text-stone-600 dark:text-stone-300">
                        <input v-model="form.remember" type="checkbox" class="size-4 rounded border-stone-300 text-brand-500 focus:ring-brand-400 dark:border-night-700 dark:bg-night-900">
                        تذكرني على هذا الجهاز
                    </label>

                    <UiButton type="submit" size="lg" class="w-full" :disabled="form.processing">
                        تسجيل الدخول
                        <Icon name="arrow-left" :size="18" />
                    </UiButton>
                </form>

                <div class="mt-6 flex items-start gap-2.5 rounded-xl border border-stone-200 bg-white/60 p-3.5 text-[11px] leading-5 text-stone-500 dark:border-night-800 dark:bg-night-900/50 dark:text-stone-400">
                    <Icon name="info" :size="16" class="mt-0.5 shrink-0 text-brand-600 dark:text-brand-300" />
                    <p>لا يوجد تسجيل عام. تُنشأ الحسابات محليًا من خلال أمر <span dir="ltr">user:create</span>.</p>
                </div>
            </div>
        </div>
    </div>
</template>
