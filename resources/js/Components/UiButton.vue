<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Icon from './Icon.vue';

const props = defineProps({
    href: String,
    type: { type: String, default: 'button' },
    variant: { type: String, default: 'brand' },
    size: { type: String, default: 'md' },
    icon: String,
    download: { type: [Boolean, String], default: false },
});

const variants = {
    brand: 'bg-brand-400 text-night-950 hover:bg-brand-300 shadow-sm',
    secondary: 'bg-stone-900 text-white hover:bg-stone-800 dark:bg-white dark:text-night-950 dark:hover:bg-stone-200',
    outline: 'border border-stone-200 bg-white text-stone-700 hover:border-stone-300 hover:bg-stone-50 dark:border-night-800 dark:bg-night-900 dark:text-stone-200 dark:hover:bg-night-800',
    ghost: 'text-stone-600 hover:bg-stone-100 dark:text-stone-300 dark:hover:bg-night-800',
    danger: 'bg-rose-600 text-white hover:bg-rose-500',
    'danger-soft': 'bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:hover:bg-rose-500/20',
    'brand-soft': 'bg-brand-100 text-brand-900 hover:bg-brand-200 dark:bg-brand-400/15 dark:text-brand-200 dark:hover:bg-brand-400/25',
};

const sizes = {
    sm: 'h-8 px-3 text-[11px]',
    md: 'h-10 px-4 text-xs',
    lg: 'h-11 px-5 text-sm',
    icon: 'size-10 p-0',
};

const classes = computed(() => [
    'inline-flex items-center justify-center gap-2 rounded-xl font-bold transition disabled:cursor-not-allowed disabled:opacity-60',
    variants[props.variant] ?? variants.brand,
    sizes[props.size] ?? sizes.md,
]);

const component = computed(() => {
    if (!props.href) {
        return 'button';
    }

    return props.download ? 'a' : Link;
});

const bindings = computed(() => {
    if (!props.href) {
        return { type: props.type };
    }

    if (props.download) {
        return {
            href: props.href,
            download: props.download === true ? '' : props.download,
        };
    }

    return { href: props.href };
});
</script>

<template>
    <component :is="component" v-bind="bindings" :class="classes">
        <Icon v-if="icon" :name="icon" :size="16" />
        <slot />
    </component>
</template>
