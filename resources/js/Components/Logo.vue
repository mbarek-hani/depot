<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import bundledLogo from '../../images/logo.png';
import Icon from './Icon.vue';

const props = defineProps({
    size: { type: String, default: 'md' },
});

const page = usePage();
const failed = ref(false);
const usingBundledLogo = ref(false);

const preferredUrl = computed(() => {
    const value = page.props.app?.logo;

    if (!value) {
        return bundledLogo;
    }

    try {
        const parsed = new URL(value, window.location.origin);

        return `${parsed.pathname}${parsed.search}`;
    } catch {
        return value;
    }
});

const url = computed(() => (usingBundledLogo.value ? bundledLogo : preferredUrl.value));

const dimensions = {
    sm: { box: 'size-9 rounded-lg', icon: 18 },
    md: { box: 'size-10 rounded-xl', icon: 20 },
    lg: { box: 'size-14 rounded-2xl', icon: 26 },
};

const dimension = computed(() => dimensions[props.size] ?? dimensions.md);

watch(preferredUrl, () => {
    usingBundledLogo.value = false;
    failed.value = false;
});

function onError() {
    if (!usingBundledLogo.value && preferredUrl.value !== bundledLogo) {
        usingBundledLogo.value = true;

        return;
    }

    failed.value = true;
}
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
            @error="onError"
        />
        <span v-else class="text-brand-600 dark:text-brand-300">
            <Icon name="archive" :size="dimension.icon" />
        </span>
    </span>
</template>
