<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import Icon from './Icon.vue';
import ThemeToggle from './ThemeToggle.vue';
import NotificationBell from './NotificationBell.vue';

defineProps({
    title: String,
});

const page = usePage();
const sidebarOpen = ref(false);
const userOpen = ref(false);
const search = ref('');
const user = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash ?? {});
const app = computed(() => page.props.app ?? {});
const errors = computed(() => page.props.errors ?? {});

const nav = [
    { route: 'dashboard', active: 'dashboard', label: 'الرئيسية', icon: 'home' },
    { route: 'products.index', active: 'products.*', label: 'المنتجات', icon: 'cube' },
    { route: 'movements.index', active: 'movements.*', label: 'الحركات', icon: 'arrows-right-left' },
    { route: 'categories.index', active: 'categories.*', label: 'الأصناف', icon: 'tag' },
    { route: 'reports.index', active: 'reports.*', label: 'التقارير', icon: 'chart-bar' },
    { route: 'notifications.index', active: 'notifications.*', label: 'الإشعارات', icon: 'bell' },
    { route: 'settings.index', active: 'settings.*', label: 'الإعدادات', icon: 'settings' },
];

const mobileNav = [
    nav[0],
    nav[1],
    nav[2],
    nav[5],
];

function isActive(item) {
    return route().current(item.active);
}

function submitSearch() {
    router.get(route('products.index'), { search: search.value }, { preserveState: true, replace: true });
}

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <Head :title="title" />

    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-[100] focus:rounded-xl focus:bg-brand-400 focus:px-4 focus:py-2 focus:font-bold focus:text-night-950">
        الانتقال إلى المحتوى
    </a>

    <aside class="fixed inset-y-0 start-0 z-40 hidden w-72 flex-col border-e border-stone-200 bg-white lg:flex dark:border-white/5 dark:bg-night-900">
        <div class="flex items-center gap-3 px-5 py-5">
            <span class="flex size-10 items-center justify-center rounded-2xl bg-brand-400 text-night-950 shadow-sm">
                <Icon name="archive" :size="22" />
            </span>
            <div class="min-w-0">
                <p class="truncate font-display text-sm font-extrabold text-stone-900 dark:text-white">{{ app.name }}</p>
                <p class="text-[10px] font-semibold text-stone-400">إدارة المخزون</p>
            </div>
        </div>

        <div class="mx-5 h-px bg-stone-200 dark:bg-white/5"></div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="التنقل الرئيسي">
            <Link
                v-for="item in nav"
                :key="item.route"
                :href="route(item.route)"
                class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-bold transition"
                :class="isActive(item)
                    ? 'bg-brand-400 text-night-950 shadow-sm'
                    : 'text-stone-500 hover:bg-stone-50 hover:text-stone-900 dark:text-stone-400 dark:hover:bg-night-800 dark:hover:text-white'"
            >
                <Icon :name="item.icon" :size="18" />
                {{ item.label }}
            </Link>
        </nav>

        <div class="border-t border-stone-100 p-3 dark:border-night-800">
            <Link :href="route('settings.index', { tab: 'account' })" class="flex items-center gap-3 rounded-xl p-2 transition hover:bg-stone-50 dark:hover:bg-night-800">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-extrabold text-brand-800 dark:bg-brand-400/15 dark:text-brand-300">
                    {{ user?.name?.slice(0, 1) }}
                </span>
                <span class="min-w-0">
                    <span class="block truncate text-xs font-bold text-stone-800 dark:text-stone-100">{{ user?.name }}</span>
                    <span class="block text-[10px] text-stone-400">حساب محلي</span>
                </span>
            </Link>
            <button
                type="button"
                class="mt-0.5 flex w-full items-center gap-2 rounded-xl px-3 py-2 text-[11px] font-bold text-rose-600 transition hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10"
                @click="logout"
            >
                <Icon name="logout" :size="16" />
                تسجيل الخروج
            </button>
        </div>
    </aside>

    <div v-if="sidebarOpen" class="fixed inset-0 z-[60] lg:hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-night-950/60 backdrop-blur-sm" @click="sidebarOpen = false"></div>

        <aside class="absolute inset-y-0 start-0 flex w-[18rem] max-w-[85vw] flex-col border-e border-stone-200 bg-white shadow-pop dark:border-white/5 dark:bg-night-900">
            <div class="flex items-center justify-between px-5 py-5">
                <div class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-2xl bg-brand-400 text-night-950">
                        <Icon name="archive" :size="20" />
                    </span>
                    <p class="font-display text-sm font-extrabold text-stone-900 dark:text-white">{{ app.name }}</p>
                </div>
                <button type="button" class="rounded-lg p-1.5 text-stone-500 hover:bg-stone-100 dark:text-stone-400 dark:hover:bg-white/10" aria-label="إغلاق القائمة" @click="sidebarOpen = false">
                    <Icon name="x" :size="20" />
                </button>
            </div>

            <div class="mx-5 h-px bg-stone-200 dark:bg-white/5"></div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <Link
                    v-for="item in nav"
                    :key="item.route"
                    :href="route(item.route)"
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-bold transition"
                    :class="isActive(item)
                        ? 'bg-brand-400 text-night-950'
                        : 'text-stone-500 hover:bg-stone-50 dark:text-stone-400 dark:hover:bg-night-800'"
                    @click="sidebarOpen = false"
                >
                    <Icon :name="item.icon" :size="18" />
                    {{ item.label }}
                </Link>
            </nav>

            <div class="border-t border-stone-100 p-3 dark:border-night-800">
                <button type="button" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10" @click="logout">
                    <Icon name="logout" :size="16" />
                    تسجيل الخروج
                </button>
            </div>
        </aside>
    </div>

    <div class="min-h-screen lg:ms-72">
        <header class="sticky top-0 z-30 border-b border-stone-200/70 bg-cream-100/85 backdrop-blur-lg dark:border-night-800 dark:bg-night-950/85">
            <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                <button
                    type="button"
                    class="inline-flex size-10 items-center justify-center rounded-xl border border-stone-200 bg-white text-stone-600 transition hover:text-stone-900 lg:hidden dark:border-night-800 dark:bg-night-900 dark:text-stone-300 dark:hover:text-white"
                    aria-label="فتح القائمة"
                    @click="sidebarOpen = true"
                >
                    <Icon name="menu" :size="20" />
                </button>

                <form class="relative hidden max-w-sm flex-1 sm:block" @submit.prevent="submitSearch">
                    <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-stone-400">
                        <Icon name="search" :size="18" />
                    </span>
                    <input
                        v-model="search"
                        type="search"
                        class="h-10 w-full rounded-xl border border-stone-200 bg-white ps-10 pe-3 text-sm text-stone-900 outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-900 dark:text-white dark:placeholder:text-stone-500"
                        placeholder="ابحث عن منتج أو رمز..."
                        aria-label="البحث في المنتجات"
                    >
                </form>

                <div class="ms-auto flex items-center gap-2">
                    <p class="me-1 hidden text-xs font-medium text-stone-500 dark:text-stone-400 xl:block">{{ app.today }}</p>

                    <ThemeToggle />
                    <NotificationBell />

                    <div class="relative">
                        <button
                            type="button"
                            class="flex items-center gap-2 rounded-xl border border-stone-200 bg-white p-1 ps-2 transition hover:border-stone-300 dark:border-night-800 dark:bg-night-900 dark:hover:border-stone-700"
                            aria-label="قائمة المستخدم"
                            @click="userOpen = !userOpen"
                        >
                            <span class="hidden max-w-28 truncate text-xs font-bold text-stone-700 sm:block dark:text-stone-200">{{ user?.name }}</span>
                            <span class="flex size-8 items-center justify-center rounded-full bg-brand-100 text-xs font-extrabold text-brand-800 dark:bg-brand-400/15 dark:text-brand-300">
                                {{ user?.name?.slice(0, 1) }}
                            </span>
                            <Icon name="chevron-down" :size="14" class="me-1 text-stone-400" />
                        </button>

                        <div v-if="userOpen" class="fixed inset-0 z-40" @click="userOpen = false"></div>

                        <div v-if="userOpen" class="absolute end-0 z-50 mt-2 w-60 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-pop dark:border-night-800 dark:bg-night-900">
                            <div class="border-b border-stone-100 px-4 py-3 dark:border-night-800">
                                <p class="truncate text-sm font-bold text-stone-900 dark:text-white">{{ user?.name }}</p>
                            </div>
                            <div class="p-1.5">
                                <Link :href="route('settings.index', { tab: 'account' })" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-50 dark:text-stone-300 dark:hover:bg-night-800" @click="userOpen = false">
                                    <Icon name="user" :size="16" />
                                    حسابي والإعدادات
                                </Link>
                                <Link :href="route('notifications.index')" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-50 dark:text-stone-300 dark:hover:bg-night-800" @click="userOpen = false">
                                    <Icon name="bell" :size="16" />
                                    الإشعارات
                                </Link>
                            </div>
                            <div class="border-t border-stone-100 p-1.5 dark:border-night-800">
                                <button type="button" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10" @click="userOpen = false; logout()">
                                    <Icon name="logout" :size="16" />
                                    تسجيل الخروج
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main id="main-content" class="mx-auto w-full max-w-7xl px-4 pb-[calc(6rem+env(safe-area-inset-bottom))] pt-6 sm:px-6 lg:px-8 lg:pb-12 lg:pt-8">
            <div v-if="flash.status || flash.error" class="mb-5 space-y-2">
                <div v-if="flash.status" class="flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                    <Icon name="check" :size="16" class="mt-0.5 shrink-0" />
                    <span>{{ flash.status }}</span>
                </div>
                <div v-if="flash.error" class="flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-800 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-200">
                    <Icon name="warning" :size="16" class="mt-0.5 shrink-0" />
                    <span>{{ flash.error }}</span>
                </div>
            </div>

            <div v-if="Object.keys(errors).length && !$slots.errors" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-800 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-200">
                {{ Object.values(errors)[0] }}
            </div>

            <slot />
        </main>
    </div>

    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-stone-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur-lg lg:hidden dark:border-night-800 dark:bg-night-900/95" aria-label="التنقل السريع">
        <div class="grid grid-cols-5">
            <Link
                v-for="item in mobileNav"
                :key="item.route"
                :href="route(item.route)"
                class="flex flex-col items-center justify-center gap-0.5 py-2.5 text-[10px] font-bold transition"
                :class="isActive(item) ? 'text-brand-700 dark:text-brand-300' : 'text-stone-400 hover:text-stone-700 dark:text-stone-500 dark:hover:text-stone-300'"
            >
                <Icon :name="item.icon" :size="20" />
                <span>{{ item.label }}</span>
            </Link>
            <button type="button" class="flex flex-col items-center justify-center gap-0.5 py-2.5 text-[10px] font-bold text-stone-400 transition hover:text-stone-700 dark:text-stone-500 dark:hover:text-stone-300" @click="sidebarOpen = true">
                <Icon name="menu" :size="20" />
                <span>المزيد</span>
            </button>
        </div>
    </nav>
</template>
