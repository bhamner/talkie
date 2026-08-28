<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface Props {
    class?: string;
    compact?: boolean;
}

withDefaults(defineProps<Props>(), {
    compact: false,
});
</script>

<template>
    <div class="flex items-center gap-2">
        <div
            class="flex aspect-square items-center justify-center rounded-2xl bg-white shadow-sm ring-2 ring-sky-200"
            :class="compact ? 'size-9' : 'size-9 lg:size-11'"
        >
            <AppLogoIcon class="size-8" />
        </div>
        <div v-if="!compact" class="hidden items-baseline leading-none lg:flex">
            <span class="text-xl font-extrabold tracking-tight text-sky-700">Talkie</span>
            <span class="text-xs font-semibold text-orange-500">.kids</span>
        </div>
    </div>
</template>
