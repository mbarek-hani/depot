<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import Icon from './Icon.vue';

const resolved = ref('light');

function sync(event) {
    resolved.value = event?.detail?.resolved ?? (document.documentElement.classList.contains('dark') ? 'dark' : 'light');
}

onMounted(() => {
    sync();
    window.addEventListener('theme-changed', sync);
});

onBeforeUnmount(() => window.removeEventListener('theme-changed', sync));

function toggle() {
    window.themeManager?.toggle();
}
</script>

<template>
    <button
        type="button"
        class="inline-flex size-10 items-center justify-center rounded-xl border border-stone-200 bg-white text-stone-600 transition hover:border-stone-300 hover:text-stone-900 dark:border-night-800 dark:bg-night-900 dark:text-stone-300 dark:hover:border-stone-700 dark:hover:text-white"
        aria-label="تبديل الوضع الليلي"
        title="تبديل المظهر"
        @click="toggle"
    >
        <Icon :name="resolved === 'dark' ? 'sun' : 'moon'" :size="20" />
    </button>
</template>
