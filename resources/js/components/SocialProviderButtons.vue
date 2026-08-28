<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Browser } from '@capacitor/browser';
import { Capacitor } from '@capacitor/core';

defineProps<{
    providers: string[];
}>();

const isNative = Capacitor.isNativePlatform();

const labels: Record<string, string> = {
    google: 'Continue with Google',
    apple: 'Continue with Apple',
};

const nativeRedirectUrl = (provider: string): string => {
    const href = route('socialite.redirect', provider);
    const url = href.startsWith('http') ? new URL(href) : new URL(href, window.location.origin);
    url.searchParams.set('native', '1');

    return url.toString();
};

const openNativeProvider = async (provider: string): Promise<void> => {
    await Browser.open({ url: nativeRedirectUrl(provider) });
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
                v-else-if="isNative"
                type="button"
                variant="outline"
                class="h-12 w-full rounded-full text-base font-extrabold"
                @click="openNativeProvider(provider)"
            >
                {{ labels[provider] ?? `Continue with ${provider}` }}
            </Button>
            <Button v-else type="button" variant="outline" class="h-12 w-full rounded-full text-base font-extrabold" as-child>
                <a :href="route('socialite.redirect', provider)">
                    {{ labels[provider] ?? `Continue with ${provider}` }}
                </a>
            </Button>
        </template>
    </div>
</template>
