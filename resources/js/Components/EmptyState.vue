<script setup>
import Icon from './Icon.vue';

defineProps({
    icon: { type: String, default: 'info' },
    title: { type: String, required: true },
    description: String,
});
</script>

<template>
    <div class="flex flex-col items-center px-6 py-12 text-center">
        <span class="flex size-12 items-center justify-center rounded-2xl bg-stone-100 text-stone-400 dark:bg-night-800 dark:text-stone-500">
            <Icon :name="icon" :size="24" />
        </span>
        <h3 class="mt-3 font-display text-base font-extrabold text-stone-900 dark:text-white">{{ title }}</h3>
        <p v-if="description" class="mt-1 max-w-sm text-xs leading-6 text-stone-500 dark:text-stone-400">{{ description }}</p>
        <div v-if="$slots.actions" class="mt-4 flex flex-wrap items-center justify-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
