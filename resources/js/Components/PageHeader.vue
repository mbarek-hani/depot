<script setup>
defineProps({
    title: { type: String, required: true },
    description: String,
    eyebrow: String,
});
</script>

<template>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <p v-if="eyebrow" class="text-[10px] font-extrabold uppercase tracking-widest text-brand-700 dark:text-brand-300">{{ eyebrow }}</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-stone-900 dark:text-white sm:text-3xl">{{ title }}</h1>
            <p v-if="description" class="mt-1 max-w-2xl text-xs leading-6 text-stone-500 dark:text-stone-400 sm:text-sm">{{ description }}</p>
        </div>
        <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
