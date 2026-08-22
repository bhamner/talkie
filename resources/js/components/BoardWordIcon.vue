<script setup lang="ts">
import {
    boardWordIconByKey,
    boardWordIconSizeAdjustment,
    boardWordLetterDisplay,
    boardWordShowsLetterOnly,
} from '@/lib/boardWordIcons';
import { boardWordLucideIconByKey } from '@/lib/boardWordLucideIcons';
import { HugeiconsIcon } from '@hugeicons/vue';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        label: string;
        icon?: string | null;
        size?: number;
        iconClass?: string;
    }>(),
    {
        size: 24,
        icon: null,
    },
);

const hugeicon = computed(() => boardWordIconByKey(props.icon));
const lucideIcon = computed(() => boardWordLucideIconByKey(props.icon));
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
        v-else-if="hugeicon"
        :icon="hugeicon"
        :size="iconSize"
        color="currentColor"
        :stroke-width="2"
        :class="iconClass"
    />
</template>
