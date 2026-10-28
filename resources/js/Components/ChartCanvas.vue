<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Chart, chartConfig } from '../charts';

const props = defineProps({
    type: { type: String, default: 'bar' },
    payload: { type: Object, required: true },
    height: { type: Number, default: 260 },
});

const canvas = ref(null);
let chart = null;

function render() {
    if (!canvas.value || !props.payload?.labels?.length) {
        return;
    }

    chart?.destroy();
    chart = new Chart(canvas.value, chartConfig(props.type, props.payload));
}

function onTheme() {
    render();
}

onMounted(() => {
    render();
    window.addEventListener('theme-changed', onTheme);
});

onBeforeUnmount(() => {
    window.removeEventListener('theme-changed', onTheme);
    chart?.destroy();
});

watch(() => props.payload, render, { deep: true });
</script>

<template>
    <div :style="{ height: `${height}px` }">
        <canvas ref="canvas"></canvas>
    </div>
</template>
