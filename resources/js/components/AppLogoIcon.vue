<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" :class="className" v-bind="$attrs" fill="none">
        <circle cx="32" cy="32" r="30" fill="#38BDF8" />
        <path
            d="M18 28c0-8 6.5-14 14-14s14 6 14 14c0 5-2.5 9-6.5 11.5L42 48l-8-4.5C24.5 42 18 36 18 28Z"
            fill="#FFF7ED"
        />
        <circle cx="27" cy="27" r="2.5" fill="#0F172A" />
        <circle cx="37" cy="27" r="2.5" fill="#0F172A" />
        <path d="M26 34c2.2 2.4 9.8 2.4 12 0" stroke="#F97316" stroke-width="2.5" stroke-linecap="round" />
        <path d="M46 18c3-1 6 1 5 4" stroke="#FBBF24" stroke-width="3" stroke-linecap="round" />
        <path d="M50 14l1.5 3.5L55 19l-3.5 1.5L50 24l-1.5-3.5L45 19l3.5-1.5L50 14Z" fill="#FBBF24" />
    </svg>
</template>
