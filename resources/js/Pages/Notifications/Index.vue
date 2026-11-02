<script setup>
import { Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Components/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import UiButton from '../../Components/UiButton.vue';
import Icon from '../../Components/Icon.vue';
import Pagination from '../../Components/Pagination.vue';
import EmptyState from '../../Components/EmptyState.vue';

defineProps({
    notifications: { type: Object, required: true },
    filter: { type: String, default: '' },
    unread: { type: Number, default: 0 },
});

function markAll() {
    router.patch(route('notifications.read-all'));
}

function deleteNotification(notification) {
    router.delete(route('notifications.destroy', notification.id), {
        preserveScroll: true,
    });
}

const toneClass = {
    warning: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300',
    danger: 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300',
    success: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300',
    info: 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300',
};
</script>

<template>
    <AppLayout title="الإشعارات">
        <PageHeader title="الإشعارات" description="تنبيهات المخزون المنخفض وتواريخ الصلاحية." eyebrow="التنبيهات">
            <template #actions>
                <UiButton v-if="unread > 0" variant="outline" icon="check" @click="markAll">تعليم الكل كمقروء</UiButton>
            </template>
        </PageHeader>

        <div class="mt-6 space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                <Link
                    :href="route('notifications.index')"
                    class="rounded-xl px-3.5 py-2 text-xs font-bold transition"
                    :class="filter !== 'unread' ? 'bg-brand-400 text-night-950' : 'border border-stone-200 bg-white text-stone-600 hover:border-stone-300 dark:border-night-800 dark:bg-night-900 dark:text-stone-300'"
                >
                    الكل
                </Link>
                <Link
                    :href="route('notifications.index', { filter: 'unread' })"
                    class="inline-flex items-center rounded-xl px-3.5 py-2 text-xs font-bold transition"
                    :class="filter === 'unread' ? 'bg-brand-400 text-night-950' : 'border border-stone-200 bg-white text-stone-600 hover:border-stone-300 dark:border-night-800 dark:bg-night-900 dark:text-stone-300'"
                >
                    غير المقروءة
                    <span v-if="unread > 0" class="ms-1 inline-flex min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 py-0.5 text-[9px] font-extrabold text-white">{{ unread > 99 ? '99+' : unread }}</span>
                </Link>
            </div>

            <EmptyState v-if="!notifications.data.length" icon="bell" title="لا توجد إشعارات" :description="filter === 'unread' ? 'قرأت جميع الإشعارات. أحسنت!' : 'ستظهر هنا تنبيهات المخزون وتواريخ الصلاحية.'" class="rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900" />

            <template v-else>
                <div class="space-y-3">
                    <div
                        v-for="notification in notifications.data"
                        :key="notification.id"
                        class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card transition hover:border-stone-300 dark:border-night-800 dark:bg-night-900 dark:hover:border-stone-700"
                    >
                        <div class="flex items-start gap-2">
                            <Link :href="notification.url" class="min-w-0 flex-1">
                                <div class="flex items-start gap-3">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl" :class="toneClass[notification.tone] ?? toneClass.info">
                                        <Icon :name="notification.icon || 'bell'" :size="20" />
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm font-bold text-stone-900 dark:text-white">{{ notification.title }}</p>
                                            <span v-if="!notification.read" class="size-2 rounded-full bg-brand-500"></span>
                                        </div>
                                        <p class="mt-1 text-xs leading-6 text-stone-600 dark:text-stone-300">{{ notification.message }}</p>
                                        <div class="mt-2 flex flex-wrap items-center gap-2 text-[10px] font-semibold text-stone-400">
                                            <span class="inline-flex items-center gap-1"><Icon name="clock" :size="12" /> {{ notification.created_human }}</span>
                                            <span v-if="notification.sku" class="font-mono">{{ notification.sku }}</span>
                                        </div>
                                    </div>
                                </div>
                            </Link>

                            <div class="flex shrink-0 items-center gap-1">
                                <button
                                    v-if="notification.read"
                                    type="button"
                                    class="inline-flex size-8 items-center justify-center rounded-lg text-rose-600 transition hover:bg-rose-50 hover:text-rose-700 dark:text-rose-300 dark:hover:bg-rose-500/10"
                                    aria-label="حذف الإشعار"
                                    @click.stop.prevent="deleteNotification(notification)"
                                >
                                    <Icon name="trash" :size="16" />
                                </button>
                                <Icon name="chevron-left" :size="18" class="text-stone-300 dark:text-stone-600" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
                    <Pagination :paginator="notifications" />
                </div>
            </template>
        </div>
    </AppLayout>
</template>
