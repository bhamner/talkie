<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Lock, Sparkles, Volume2 } from 'lucide-vue-next';

export type CatalogVoice = {
    id: string;
    name: string;
    description: string;
    tier: string;
    provider: string;
    engine?: string | null;
    model?: string | null;
    speaker_id?: number | null;
    preview_text: string;
    selectable: boolean;
    lock_reason?: string | null;
};

defineProps<{
    voices: CatalogVoice[];
    modelValue: string;
    previewing?: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
    preview: [voice: CatalogVoice];
}>();

const select = (voice: CatalogVoice) => {
    if (!voice.selectable) {
        return;
    }

    emit('update:modelValue', voice.id);
};

const tierLabel = (tier: string): string => {
    if (tier === 'premium') {
        return 'App';
    }

    if (tier === 'neural') {
        return 'Neural';
    }

    return 'Free';
};

const tierClass = (tier: string): string => {
    if (tier === 'premium') {
        return 'bg-amber-200 text-amber-900';
    }

    if (tier === 'neural') {
        return 'bg-sky-200 text-sky-900';
    }

    return 'bg-emerald-200 text-emerald-900';
};

const lockCopy = (reason: string | null | undefined): string => {
    if (reason === 'app') {
        return 'Included in the Talkie app';
    }

    return 'Sign in to use';
};
</script>

<template>
    <div class="flex flex-col gap-3">
        <div class="grid gap-3">
            <button
                v-for="voice in voices"
                :key="voice.id"
                type="button"
                class="rounded-3xl border-2 p-4 text-left transition"
                :class="[
                    modelValue === voice.id && voice.selectable ? 'border-orange-400 bg-orange-50 ring-4 ring-orange-200' : 'border-sky-200 bg-white',
                    voice.selectable ? 'hover:border-sky-300 hover:bg-sky-50' : 'cursor-not-allowed opacity-80',
                ]"
                @click="select(voice)"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-extrabold text-slate-800">{{ voice.name }}</h3>
                            <span
                                v-if="voice.tier !== 'free'"
                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-extrabold"
                                :class="tierClass(voice.tier)"
                            >
                                <Sparkles class="h-3 w-3" />
                                {{ tierLabel(voice.tier) }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm font-semibold text-slate-600">{{ voice.description }}</p>
                        <p v-if="!voice.selectable" class="mt-2 text-xs font-extrabold uppercase tracking-wide text-rose-500">
                            {{ lockCopy(voice.lock_reason) }}
                        </p>
                    </div>
                    <Lock v-if="!voice.selectable" class="mt-1 h-5 w-5 shrink-0 text-slate-400" />
                </div>

                <div class="mt-3">
                    <Button
                        type="button"
                        size="sm"
                        variant="secondary"
                        class="rounded-full font-bold"
                        :disabled="!voice.selectable || previewing"
                        @click.stop="emit('preview', voice)"
                    >
                        <Volume2 class="mr-2 h-4 w-4" />
                        {{ previewing ? 'Loading…' : 'Preview' }}
                    </Button>
                </div>
            </button>
        </div>

        <div>
            <p class="text-sm font-semibold text-slate-600">More voices available in the app</p>
            <div class="mt-2 flex flex-wrap items-center gap-4">
                <a class="inline-flex cursor-not-allowed items-center gap-1.5 text-sm font-bold text-slate-600" aria-disabled="true" tabindex="-1">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                        <path
                            fill="#111827"
                            d="M16.365 1.43c0 1.14-.413 2.21-1.164 3.05-.87.98-2.29 1.74-3.46 1.64-.155-1.07.392-2.2 1.116-3.05C13.66 2.15 15.14 1.47 16.365 1.43zm4.389 15.908c-.564 1.323-.83 1.91-1.55 3.08-1.007 1.61-2.426 3.61-4.172 3.63-1.517.02-1.91-1.008-4.003-1-2.084.01-2.51 1.01-4.027.99-1.746-.02-3.083-1.83-4.09-3.44C1.3 16.82.82 12.37 2.62 9.338c.79-1.33 2.17-2.18 3.68-2.21 1.45-.03 2.37.78 3.59.78 1.2 0 1.94-.79 3.67-.79 1.46 0 3.01.8 4.1 2.17-3.64 2.01-3.05 7.23.764 8.64z"
                        />
                    </svg>
                    App Store
                </a>
                <a class="inline-flex cursor-not-allowed items-center gap-1.5 text-sm font-bold text-slate-600" aria-disabled="true" tabindex="-1">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#EA4335" d="M3 20.5v-17c0-.4.2-.7.5-.9l9.7 9.4L3.5 21.4c-.3-.2-.5-.5-.5-.9z" />
                        <path fill="#FBBC04" d="m13.2 12 3.2-3.1L4.3 1.3C3.9 1.1 3.4 1.1 3 1.4L13.2 12z" />
                        <path fill="#4285F4" d="M20.5 10.9 16.4 8.9l-3.2 3.1 3.2 3.1 4.1-2c.7-.4.7-1.4 0-1.8z" />
                        <path fill="#34A853" d="M13.2 12 3 21.6c.4.3.9.3 1.3.1L16.4 15.1 13.2 12z" />
                    </svg>
                    Play Store
                </a>
            </div>
        </div>
    </div>
</template>
