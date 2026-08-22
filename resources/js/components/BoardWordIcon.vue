<script setup lang="ts">
import {
    boardWordIcon,
    boardWordIconSizeAdjustment,
    boardWordLetterDisplay,
    boardWordShowsLetterOnly,
} from '@/lib/boardWordIcons';
import { boardWordLucideIcon } from '@/lib/boardWordLucideIcons';
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
const lucideIcon = computed(() => boardWordLucideIcon(props.label));
const showsLetterOnly = computed(() => boardWordShowsLetterOnly(props.label));
const letterDisplay = computed(() => boardWordLetterDisplay(props.label));
const iconSize = computed(() => props.size + boardWordIconSizeAdjustment(props.label));
</script>

<template>
    <span
        v-if="showsLetterOnly"
        class="font-extrabold leading-none"
        :class="iconClass"
        :style="{ fontSize: `${Math.round(iconSize * 0.85)}px` }"
    >
        {{ letterDisplay }}
    </span>
    <component
        :is="lucideIcon"
        v-else-if="lucideIcon"
        :size="iconSize"
        :stroke-width="2"
        :class="iconClass"
    />
    <HugeiconsIcon
        v-else-if="icon"
        :icon="icon"
        :size="iconSize"
        color="currentColor"
        :stroke-width="2"
        :class="iconClass"
    />
</template>
