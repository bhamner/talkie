<script setup lang="ts">
import { piperProgress } from '@/lib/piperTts';
import { LoaderCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const visible = computed(() => piperProgress.phase === 'downloading' || piperProgress.phase === 'preparing');

const percent = computed(() => {
    if (piperProgress.phase !== 'downloading' || piperProgress.total <= 0) {
        return null;
    }

    return Math.min(100, Math.round((piperProgress.loaded / piperProgress.total) * 100));
});

const title = computed(() => {
    if (percent.value !== null) {
        return `Downloading Piper voice… ${percent.value}%`;
    }

    if (piperProgress.phase === 'downloading') {
        return 'Downloading Piper voice…';
    }

    return 'Getting Piper ready…';
});
</script>

<template>
    <div
        v-if="visible"
        class="rounded-2xl border-2 border-sky-300 bg-sky-50 px-3 py-3 text-sky-950"
        role="status"
        aria-live="polite"
        aria-busy="true"
    >
        <div class="flex items-start gap-2">
            <LoaderCircle class="mt-0.5 h-5 w-5 shrink-0 animate-spin text-sky-700" />
            <div class="min-w-0">
                <p class="text-sm font-extrabold">{{ title }}</p>
                <p class="mt-1 text-xs font-semibold text-sky-800">
                    This happens once on this device (~80 MB). Keep this page open — words will speak when it finishes.
                </p>
            </div>
        </div>
        <div v-if="percent !== null" class="mt-2 h-2 overflow-hidden rounded-full bg-sky-200">
            <div class="h-full rounded-full bg-sky-600 transition-[width] duration-200" :style="{ width: `${percent}%` }" />
        </div>
    </div>
</template>
