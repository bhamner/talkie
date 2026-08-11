<script setup lang="ts">
import AppFullscreenLayout from '@/layouts/app/AppFullscreenLayout.vue';
</script>

<template>
    <AppFullscreenLayout>
        <template v-if="$slots.headerActions" #headerActions>
            <slot name="headerActions" />
        </template>
        <slot />
    </AppFullscreenLayout>
</template>
