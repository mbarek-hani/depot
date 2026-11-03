<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import Icon from './Icon.vue';

const page = usePage();
const open = ref(false);
const bellMenu = ref(null);
const unread = ref(Number(page.props.notifications?.unread ?? 0));
const latest = ref([...(page.props.notifications?.latest ?? [])]);
let timer = null;
let refreshRequest = 0;
let echoConnection = null;

const badge = computed(() => (unread.value > 99 ? '99+' : String(unread.value)));
const userId = computed(() => page.props.auth?.user?.id);

async function refresh() {
    const request = ++refreshRequest;

    try {
        const response = await fetch(route('notifications.unread-count'), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            cache: 'no-store',
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();

        if (request !== refreshRequest) {
            return;
        }

        unread.value = Number(data.unread || 0);

        if (Array.isArray(data.latest)) {
            latest.value = data.latest;
        }
    } catch {
        //
    }
}

function refreshIfVisible() {
    if (document.visibilityState === 'visible') {
        refresh();
    }
}

function onNotification(event) {
    if (!event?.id) {
        return;
    }

    const isNew = !latest.value.some((item) => item.id === event.id);

    latest.value = [event, ...latest.value.filter((item) => item.id !== event.id)].slice(0, 5);

    if (isNew) {
        unread.value += 1;
    }

    refresh();
}

function onKey(event) {
    if (event.key === 'Escape') {
        open.value = false;
    }
}

function onPointerDown(event) {
    if (open.value && bellMenu.value && !bellMenu.value.contains(event.target)) {
        open.value = false;
    }
}

watch(
    () => page.props.notifications,
    (value) => {
        if (!value) {
            return;
        }

        unread.value = Number(value.unread ?? 0);
        latest.value = [...(value.latest ?? [])];
    },
    { deep: true },
);

watch(open, (value) => {
    if (value) {
        refresh();
    }
});

onMounted(() => {
    refresh();
    timer = window.setInterval(refreshIfVisible, 15000);

    if (window.Echo && userId.value) {
        window.Echo.private(`users.${userId.value}`).listen('.notification.created', onNotification);
        echoConnection = window.Echo.connector?.pusher?.connection ?? null;
        echoConnection?.bind('connected', refresh);
    }

    window.addEventListener('keydown', onKey);
    window.addEventListener('focus', refresh);
    window.addEventListener('online', refresh);
    document.addEventListener('pointerdown', onPointerDown);
    document.addEventListener('visibilitychange', refreshIfVisible);
});

onBeforeUnmount(() => {
    window.clearInterval(timer);

    if (window.Echo && userId.value) {
        window.Echo.leave(`users.${userId.value}`);
    }

    echoConnection?.unbind('connected', refresh);

    window.removeEventListener('keydown', onKey);
    window.removeEventListener('focus', refresh);
    window.removeEventListener('online', refresh);
    document.removeEventListener('pointerdown', onPointerDown);
    document.removeEventListener('visibilitychange', refreshIfVisible);
});
</script>

<template>
    <div ref="bellMenu" class="relative">
        <button
            type="button"
            class="relative inline-flex size-10 items-center justify-center rounded-xl border border-stone-200 bg-white text-stone-600 transition hover:border-stone-300 hover:text-stone-900 dark:border-night-800 dark:bg-night-900 dark:text-stone-300 dark:hover:border-stone-700 dark:hover:text-white"
            aria-label="الإشعارات"
            :aria-expanded="open"
            @click="open = !open"
        >
            <Icon name="bell" :size="20" />
            <span
                v-if="unread > 0"
                class="absolute -end-1 -top-1 inline-flex min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 py-0.5 text-[9px] leading-none font-extrabold text-white"
            >
                {{ badge }}
            </span>
        </button>

        <div
            v-if="open"
            class="fixed inset-x-3 top-[4.5rem] z-50 flex max-h-[calc(100dvh-6rem)] flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-pop sm:absolute sm:inset-x-auto sm:end-0 sm:top-full sm:mt-2 sm:max-h-none sm:w-80 dark:border-night-800 dark:bg-night-900"
        >
            <div
                class="flex shrink-0 items-center justify-between border-b border-stone-100 px-4 py-3 dark:border-night-800"
            >
                <p class="text-sm font-extrabold text-stone-900 dark:text-white">الإشعارات</p>
                <span
                    v-if="unread > 0"
                    class="rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-bold text-rose-600 dark:bg-rose-500/10 dark:text-rose-300"
                >
                    {{ unread }} جديد
                </span>
            </div>

            <div v-if="latest.length" class="min-h-0 flex-1 overflow-y-auto sm:max-h-80 sm:flex-none">
                <div
                    v-for="item in latest"
                    :key="item.id"
                    class="border-b border-stone-50 transition last:border-0 hover:bg-stone-50 dark:border-night-800/50 dark:hover:bg-night-800/50"
                >
                    <Link :href="item.url" class="block px-4 py-3" @click="open = false">
                        <div class="flex items-start gap-2.5">
                            <span class="mt-0.5 size-2 shrink-0 rounded-full bg-brand-500"></span>
                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold text-stone-800 dark:text-stone-100">
                                    {{ item.title }}
                                </p>
                                <p class="mt-0.5 line-clamp-2 text-[11px] leading-5 text-stone-500 dark:text-stone-400">
                                    {{ item.message }}
                                </p>
                                <p class="mt-1 text-[10px] text-stone-400">{{ item.created_human }}</p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <p v-else class="px-4 py-8 text-center text-xs text-stone-400">لا توجد إشعارات حاليًا.</p>

            <Link
                :href="route('notifications.index')"
                class="block shrink-0 border-t border-stone-100 px-4 py-2.5 text-center text-[11px] font-bold text-brand-700 hover:bg-stone-50 dark:border-night-800 dark:text-brand-300 dark:hover:bg-night-800/50"
                @click="open = false"
            >
                عرض كل الإشعارات
            </Link>
        </div>
    </div>
</template>
