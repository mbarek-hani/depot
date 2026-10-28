<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    paginator: { type: Object, required: true },
});
</script>

<template>
    <div v-if="paginator.total > 0" class="flex flex-col items-center justify-between gap-3 border-t border-stone-100 px-4 py-3 dark:border-night-800 sm:flex-row">
        <p class="text-[11px] font-medium text-stone-500 dark:text-stone-400">
            عرض {{ paginator.from }}–{{ paginator.to }} من {{ paginator.total }}
        </p>

        <div v-if="paginator.links.length > 3" class="flex flex-wrap items-center gap-1">
            <template v-for="link in paginator.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    class="inline-flex min-w-8 items-center justify-center rounded-lg border px-2.5 py-1.5 text-[11px] font-bold transition"
                    :class="link.active
                        ? 'border-brand-400 bg-brand-400 text-night-950'
                        : 'border-stone-200 bg-white text-stone-600 hover:border-stone-300 dark:border-night-800 dark:bg-night-900 dark:text-stone-300'"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="inline-flex min-w-8 items-center justify-center rounded-lg border border-transparent px-2.5 py-1.5 text-[11px] font-bold text-stone-300 dark:text-stone-600"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>
