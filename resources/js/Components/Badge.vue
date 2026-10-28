<script setup>
import { computed } from 'vue';

const props = defineProps({
    tone: { type: String, default: 'neutral' },
    dot: Boolean,
});

const tones = {
    neutral: 'bg-stone-100 text-stone-600 dark:bg-night-800 dark:text-stone-300',
    brand: 'bg-brand-100 text-brand-800 dark:bg-brand-400/15 dark:text-brand-300',
    success: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
    warning: 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
    danger: 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
    info: 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300',
};

const classes = computed(() => tones[props.tone] ?? tones.neutral);
</script>

<template>
    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold" :class="classes">
        <span v-if="dot" class="size-1.5 rounded-full bg-current"></span>
        <slot />
    </span>
</template>
