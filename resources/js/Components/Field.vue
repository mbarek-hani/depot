<script setup>
defineProps({
    label: String,
    error: String,
    hint: String,
    required: Boolean,
});
</script>

<template>
    <label class="block space-y-1.5">
        <span v-if="label" class="block text-sm font-semibold text-stone-700 dark:text-stone-200">
            {{ label }}
            <span v-if="required" class="text-rose-500">*</span>
        </span>
        <slot />
        <span v-if="error" class="block text-[11px] font-medium text-rose-600 dark:text-rose-400">{{ error }}</span>
        <span v-else-if="hint" class="block text-[10px] leading-5 text-stone-400">{{ hint }}</span>
    </label>
</template>
