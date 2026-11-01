<script setup>
import { reactive } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Components/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import StatCard from '../../Components/StatCard.vue';
import Badge from '../../Components/Badge.vue';
import UiButton from '../../Components/UiButton.vue';
import Icon from '../../Components/Icon.vue';
import Pagination from '../../Components/Pagination.vue';
import EmptyState from '../../Components/EmptyState.vue';

const props = defineProps({
    users: { type: Object, required: true },
    filters: { type: Object, required: true },
    summary: { type: Object, required: true },
});

const page = usePage();

const filters = reactive({
    search: props.filters.search ?? '',
    role: props.filters.role ?? '',
    status: props.filters.status ?? '',
});

const inputClass = 'h-10 w-full rounded-xl border border-stone-300 bg-white px-3.5 text-xs text-stone-900 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-950 dark:text-white';

function apply() {
    router.get(route('users.index'), { ...filters }, { preserveState: true, replace: true });
}

function clear() {
    router.get(route('users.index'));
}
</script>

<template>
    <AppLayout title="المستخدمون">
        <PageHeader title="المستخدمون" description="أنشئ حسابات المستخدمين وتحكم في حالتها وكلمات المرور." eyebrow="الإدارة">
            <template #actions>
                <UiButton :href="route('users.create')" icon="plus">إضافة مستخدم</UiButton>
            </template>
        </PageHeader>

        <div class="mt-6 space-y-4">
            <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
                <StatCard label="إجمالي المستخدمين" :value="summary.total" />
                <StatCard label="مديرو النظام" :value="summary.admins" />
                <StatCard label="الحسابات النشطة" :value="summary.active" />
                <StatCard label="الحسابات الموقوفة" :value="summary.blocked" />
            </div>

            <div class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
                <form class="grid gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end" @submit.prevent="apply">
                    <div class="relative lg:col-span-6">
                        <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-stone-400"><Icon name="search" :size="16" /></span>
                        <input v-model="filters.search" :class="inputClass" class="ps-9" placeholder="ابحث باسم المستخدم..." aria-label="البحث في المستخدمين">
                    </div>
                    <select v-model="filters.role" :class="inputClass" class="lg:col-span-2" aria-label="تصفية حسب الدور">
                        <option value="">كل الأدوار</option>
                        <option value="admin">مدير النظام</option>
                        <option value="user">مستخدم</option>
                    </select>
                    <select v-model="filters.status" :class="inputClass" class="lg:col-span-2" aria-label="تصفية حسب الحالة">
                        <option value="">كل الحالات</option>
                        <option value="active">نشط</option>
                        <option value="blocked">موقوف</option>
                    </select>
                    <UiButton type="submit" icon="filter" class="lg:col-span-2">تصفية</UiButton>
                </form>

                <button v-if="filters.search || filters.role || filters.status" type="button" class="mt-3 inline-flex items-center gap-1 text-[11px] font-bold text-brand-700 hover:underline dark:text-brand-300" @click="clear">
                    <Icon name="x" :size="14" /> مسح الفلاتر
                </button>
            </div>

            <EmptyState v-if="!users.data.length" title="لا يوجد مستخدمون مطابقون" description="جرّب تغيير الفلاتر أو أضف مستخدمًا جديدًا." class="rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
                <template #actions>
                    <UiButton :href="route('users.create')" icon="plus">إضافة مستخدم</UiButton>
                </template>
            </EmptyState>

            <template v-else>
                <div class="hidden overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card lg:block dark:border-night-800 dark:bg-night-900">
                    <div class="overflow-x-auto scrollbar-thin">
                        <table class="w-full min-w-[52rem] text-start">
                            <thead>
                                <tr class="border-b border-stone-100 bg-stone-50/70 text-[11px] font-bold text-stone-500 dark:border-night-800 dark:bg-night-950/60 dark:text-stone-400">
                                    <th class="px-5 py-3.5 text-start">المستخدم</th>
                                    <th class="px-4 py-3.5 text-start">الدور</th>
                                    <th class="px-4 py-3.5 text-start">الحالة</th>
                                    <th class="px-4 py-3.5 text-start">آخر دخول</th>
                                    <th class="px-4 py-3.5 text-start">الحركات</th>
                                    <th class="px-5 py-3.5 text-end">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 dark:divide-night-800">
                                <tr v-for="user in users.data" :key="user.id" class="transition hover:bg-stone-50/60 dark:hover:bg-night-800/30">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-extrabold text-brand-800 dark:bg-brand-400/15 dark:text-brand-300">{{ user.name.slice(0, 1) }}</span>
                                            <div class="min-w-0">
                                                <p class="max-w-56 truncate text-xs font-bold text-stone-900 dark:text-white">
                                                    {{ user.name }}
                                                    <span v-if="user.id === page.props.auth?.user?.id" class="text-[10px] font-medium text-brand-700 dark:text-brand-300">(أنت)</span>
                                                </p>
                                                <p class="mt-0.5 text-[10px] text-stone-400">{{ user.is_admin ? 'حساب مدير' : 'حساب مستخدم' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5"><Badge :tone="user.role_tone">{{ user.role_label }}</Badge></td>
                                    <td class="px-4 py-3.5"><Badge :tone="user.status_tone" dot>{{ user.status_label }}</Badge></td>
                                    <td class="px-4 py-3.5">
                                        <p class="text-xs font-semibold text-stone-700 dark:text-stone-200">{{ user.last_login_formatted }}</p>
                                        <p class="mt-0.5 text-[10px] text-stone-400">{{ user.last_login_human }}</p>
                                    </td>
                                    <td class="px-4 py-3.5 text-xs font-bold text-stone-700 tabular dark:text-stone-200">{{ user.movements_count }}</td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex justify-end">
                                            <UiButton v-if="!user.is_admin" :href="route('users.edit', user.id)" variant="ghost" size="icon" icon="pencil" aria-label="تعديل المستخدم" />
                                            <span v-else class="text-[10px] font-semibold text-stone-400">يُدار من الطرفية</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :paginator="users" />
                </div>

                <div class="space-y-3 lg:hidden">
                    <article v-for="user in users.data" :key="user.id" class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-extrabold text-brand-800 dark:bg-brand-400/15 dark:text-brand-300">{{ user.name.slice(0, 1) }}</span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-stone-900 dark:text-white">{{ user.name }}</p>
                                    <p class="mt-0.5 text-[10px] text-stone-400">{{ user.last_login_human }}</p>
                                </div>
                            </div>
                            <Badge :tone="user.status_tone" dot>{{ user.status_label }}</Badge>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <Badge :tone="user.role_tone">{{ user.role_label }}</Badge>
                            <Badge tone="neutral">{{ user.movements_count }} حركة</Badge>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-3 rounded-xl bg-stone-50 px-3 py-2.5 text-[11px] dark:bg-night-950">
                            <span class="text-stone-400">آخر دخول</span>
                            <span class="font-semibold text-stone-700 dark:text-stone-200">{{ user.last_login_formatted }}</span>
                        </div>

                        <div v-if="!user.is_admin" class="mt-3 flex justify-end">
                            <UiButton :href="route('users.edit', user.id)" variant="brand-soft" size="sm" icon="pencil">تعديل</UiButton>
                        </div>
                    </article>

                    <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
                        <Pagination :paginator="users" />
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
