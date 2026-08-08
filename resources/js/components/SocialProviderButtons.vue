<script setup lang="ts">
import { Button } from '@/components/ui/button';

defineProps<{
    providers: string[];
}>();

const labels: Record<string, string> = {
    google: 'Continue with Google',
    apple: 'Continue with Apple',
    facebook: 'Continue with Facebook',
};
</script>

<template>
    <div class="grid gap-2">
        <Button
            v-for="provider in providers"
            :key="provider"
            type="button"
            variant="outline"
            class="h-12 w-full rounded-full text-base font-extrabold"
            as-child
        >
            <a :href="route('socialite.redirect', provider)">
                {{ labels[provider] ?? `Continue with ${provider}` }}
            </a>
        </Button>
    </div>
</template>
