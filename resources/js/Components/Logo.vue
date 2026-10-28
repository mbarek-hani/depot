<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Icon from './Icon.vue';

const props = defineProps({
    size: { type: String, default: 'md' },
});

const page = usePage();
const failed = ref(false);

const url = computed(() => page.props.app?.logo ?? null);

const dimensions = {
    sm: { box: 'size-9 rounded-lg', icon: 18 },
    md: { box: 'size-10 rounded-xl', icon: 20 },
    lg: { box: 'size-14 rounded-2xl', icon: 26 },
};

const dimension = computed(() => dimensions[props.size] ?? dimensions.md);

watch(url, () => {
    failed.value = false;
});
</script>

<template>
    <span
        :class="[
            dimension.box,
            'inline-flex shrink-0 items-center justify-center overflow-hidden bg-white shadow-sm ring-1 ring-stone-200/70 dark:bg-night-950 dark:ring-night-800',
        ]"
    >
        <img
            v-if="url && !failed"
            :src="url"
            :alt="page.props.app?.name ?? ''"
            class="size-full object-contain"
            @error="failed = true"
        />
        <span v-else class="text-brand-600 dark:text-brand-300">
            <Icon name="archive" :size="dimension.icon" />
        </span>
    </span>
</template>
