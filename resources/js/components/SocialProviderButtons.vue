<script setup lang="ts">
import { Button } from '@/components/ui/button';

defineProps<{
    providers: string[];
}>();

const labels: Record<string, string> = {
    google: 'Continue with Google',
    apple: 'Continue with Apple',
};
</script>

<template>
    <div class="grid gap-2">
        <template v-for="provider in providers" :key="provider">
            <Button
                v-if="provider === 'apple'"
                type="button"
                variant="outline"
                disabled
                class="h-12 w-full rounded-full text-base font-extrabold opacity-60"
            >
                <span class="flex w-full items-center justify-center gap-2">
                    {{ labels.apple }}
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Coming soon</span>
                </span>
            </Button>
            <Button
                v-else
                type="button"
                variant="outline"
                class="h-12 w-full rounded-full text-base font-extrabold"
                as-child
            >
                <a :href="route('socialite.redirect', provider)">
                    {{ labels[provider] ?? `Continue with ${provider}` }}
                </a>
            </Button>
        </template>
    </div>
</template>
