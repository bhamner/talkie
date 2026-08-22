<script setup lang="ts">
import { boardFolderIcon } from '@/lib/boardFolderIcons';
import { HugeiconsIcon } from '@hugeicons/vue';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        name: string;
        size?: number;
        iconClass?: string;
    }>(),
    {
        size: 32,
    },
);

const icon = computed(() => boardFolderIcon(props.name));
</script>

<template>
    <HugeiconsIcon
        v-if="icon"
        :icon="icon"
        :size="size"
        color="currentColor"
        :stroke-width="2"
        :class="iconClass"
    />
</template>
