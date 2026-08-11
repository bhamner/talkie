<script setup lang="ts">
import { boardWordIcon } from '@/lib/boardWordIcons';
import { HugeiconsIcon } from '@hugeicons/vue';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        label: string;
        size?: number;
        iconClass?: string;
    }>(),
    {
        size: 24,
    },
);

const icon = computed(() => boardWordIcon(props.label));
</script>

<template>
    <HugeiconsIcon
        :icon="icon"
        :size="size"
        color="currentColor"
        :stroke-width="2"
        :class="iconClass"
    />
</template>
