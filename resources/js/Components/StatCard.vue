<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    label: String,
    value: [String, Number],
    hint: String,
    tone: { type: String, default: 'neutral' },
    href: String,
});

const tones = {
    neutral: 'border-stone-200/80 bg-white text-stone-900 dark:border-night-800 dark:bg-night-900 dark:text-white',
    brand: 'border-brand-200 bg-brand-50 text-brand-900 dark:border-brand-400/20 dark:bg-brand-400/10 dark:text-brand-200',
    info: 'border-sky-200 bg-sky-50 text-sky-900 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-200',
    success: 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200',
    warning: 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-400/20 dark:bg-amber-500/10 dark:text-amber-200',
    danger: 'border-rose-200 bg-rose-50 text-rose-900 dark:border-rose-400/20 dark:bg-rose-500/10 dark:text-rose-200',
};

const cardClass = computed(() => ['block rounded-2xl border p-4 shadow-card transition sm:p-5', tones[props.tone] ?? tones.neutral]);
</script>

<template>
    <component :is="href ? Link : 'div'" :href="href" :class="cardClass">
        <p class="text-[11px] font-semibold opacity-70">{{ label }}</p>
        <p class="mt-1.5 font-display text-2xl font-extrabold tabular">{{ value }}</p>
        <p v-if="hint" class="mt-1 text-[10px] font-medium opacity-60">{{ hint }}</p>
    </component>
</template>
