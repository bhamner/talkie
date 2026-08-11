<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

defineProps<{
    title?: string;
    description?: string;
    showTypingKeyboard?: boolean;
}>();
</script>

<template>
    <AuthLayout :title="title" :description="description" :show-typing-keyboard="showTypingKeyboard">
        <slot />
    </AuthLayout>
</template>
