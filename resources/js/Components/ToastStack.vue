<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Icon from './Icon.vue';

const page = usePage();
const toasts = ref([]);
const timers = new Map();
let sequence = 0;

const styles = {
    success: {
        card: 'border-emerald-200/80 bg-white text-stone-800 dark:border-emerald-500/25 dark:bg-night-900 dark:text-stone-100',
        icon: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300',
        name: 'check',
        label: 'تم بنجاح',
    },
    error: {
        card: 'border-rose-200/80 bg-white text-stone-800 dark:border-rose-500/25 dark:bg-night-900 dark:text-stone-100',
        icon: 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300',
        name: 'warning',
        label: 'حدث خطأ',
    },
};

function dismiss(id) {
    const timer = timers.get(id);

    if (timer) {
        window.clearTimeout(timer);
        timers.delete(id);
    }

    toasts.value = toasts.value.filter((toast) => toast.id !== id);
}

function push(message, tone) {
    if (!message) {
        return;
    }

    const id = ++sequence;
    toasts.value.push({ id, message: String(message), tone });

    timers.set(id, window.setTimeout(() => dismiss(id), 5000));
}

function consume(flash, errors) {
    const status = flash?.status;
    const error = flash?.error;

    if (status) {
        push(status, 'success');
    }

    if (error) {
        push(error, 'error');
    } else if (errors && Object.keys(errors).length) {
        push(Object.values(errors)[0], 'error');
    }
}

watch(
    [() => page.props.flash, () => page.props.errors],
    ([flash, errors]) => consume(flash, errors),
    { deep: true, immediate: true },
);

onBeforeUnmount(() => {
    timers.forEach((timer) => window.clearTimeout(timer));
    timers.clear();
});
</script>

<template>
    <Teleport to="body">
        <div
            class="pointer-events-none fixed right-4 top-4 z-[100] flex w-[calc(100vw-2rem)] max-w-sm flex-col gap-2"
            aria-live="polite"
            aria-atomic="false"
        >
            <TransitionGroup name="toast">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="[
                        styles[toast.tone].card,
                        'pointer-events-auto flex items-start gap-3 rounded-2xl border p-3.5 shadow-pop',
                    ]"
                    :role="toast.tone === 'error' ? 'alert' : 'status'"
                    dir="rtl"
                >
                    <span :class="[styles[toast.tone].icon, 'flex size-8 shrink-0 items-center justify-center rounded-xl']">
                        <Icon :name="styles[toast.tone].name" :size="17" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-extrabold text-stone-900 dark:text-white">{{ styles[toast.tone].label }}</p>
                        <p class="mt-0.5 text-xs font-semibold leading-5 text-stone-600 dark:text-stone-300">{{ toast.message }}</p>
                    </div>

                    <button
                        type="button"
                        class="flex size-7 shrink-0 items-center justify-center rounded-lg text-stone-400 transition hover:bg-stone-100 hover:text-stone-700 dark:text-stone-500 dark:hover:bg-white/10 dark:hover:text-white"
                        aria-label="إغلاق التنبيه"
                        @click="dismiss(toast.id)"
                    >
                        <Icon name="x" :size="15" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateX(1rem);
}

.toast-move {
    transition: transform 0.25s ease;
}
</style>
