<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    label: String,
    value: [String, Number],
    hint: String,
    href: String,
});
</script>

<template>
    <component
        :is="href ? Link : 'div'"
        v-bind="href ? { href } : {}"
        class="block rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card transition sm:p-5 dark:border-night-800 dark:bg-night-900"
    >
        <p class="text-[11px] font-semibold text-stone-500 dark:text-stone-400">{{ label }}</p>
        <p class="mt-1.5 font-display text-2xl font-extrabold tabular text-stone-900 dark:text-white">{{ value }}</p>
        <p v-if="hint" class="mt-1 text-[10px] font-medium text-stone-400 dark:text-stone-500">{{ hint }}</p>
    </component>
</template>
