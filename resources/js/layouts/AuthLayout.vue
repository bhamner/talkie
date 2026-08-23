<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

defineProps<{
    title?: string;
    description?: string;
    showTypingKeyboard?: boolean;
    showLegalFooter?: boolean;
}>();
</script>

<template>
    <AuthLayout
        :title="title"
        :description="description"
        :show-typing-keyboard="showTypingKeyboard"
        :show-legal-footer="showLegalFooter"
    >
        <slot />
    </AuthLayout>
</template>
