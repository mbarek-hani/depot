<script setup>
import Icon from './Icon.vue';

defineProps({
    open: Boolean,
    title: String,
    description: String,
    size: { type: String, default: 'md' },
});

const emit = defineEmits(['close']);

const sizes = {
    sm: 'max-w-md',
    md: 'max-w-xl',
    lg: 'max-w-2xl',
};
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-[80] flex items-end justify-center p-0 sm:items-center sm:p-6" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-night-950/60 backdrop-blur-sm" @click="emit('close')"></div>

            <div class="relative flex max-h-[92vh] w-full flex-col overflow-hidden rounded-t-3xl border border-stone-200 bg-white shadow-pop dark:border-night-800 dark:bg-night-900 sm:rounded-2xl" :class="sizes[size] ?? sizes.md">
                <div class="flex items-start justify-between gap-3 border-b border-stone-100 px-5 py-4 dark:border-night-800">
                    <div>
                        <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">{{ title }}</h2>
                        <p v-if="description" class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">{{ description }}</p>
                    </div>
                    <button type="button" class="rounded-lg p-1.5 text-stone-400 hover:bg-stone-100 dark:hover:bg-night-800" aria-label="إغلاق" @click="emit('close')">
                        <Icon name="x" :size="20" />
                    </button>
                </div>

                <div class="overflow-y-auto p-5">
                    <slot />
                </div>

                <div v-if="$slots.footer" class="flex items-center justify-end gap-2 border-t border-stone-100 px-5 py-3 dark:border-night-800">
                    <slot name="footer" />
                </div>
            </div>
        </div>
    </Teleport>
</template>
