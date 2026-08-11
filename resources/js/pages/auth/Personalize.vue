<script setup lang="ts">
import SocialProviderButtons from '@/components/SocialProviderButtons.vue';
import TextLink from '@/components/TextLink.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    providers: string[];
}>();
</script>

<template>
    <AuthBase
        title="Personalize Talkie"
        description="Sign in with Google, Apple, or Facebook to save your name, voice, and custom word board."
        :show-typing-keyboard="false"
    >
        <Head title="Personalize" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <div class="flex flex-col gap-6">
            <SocialProviderButtons :providers="providers" />

            <p class="text-center text-sm font-semibold text-slate-500">
                Talkie uses secure single sign-on only — no passwords to remember.
            </p>

            <div class="text-center text-sm">
                <TextLink :href="route('board')">Back to board</TextLink>
            </div>
        </div>
    </AuthBase>
</template>
