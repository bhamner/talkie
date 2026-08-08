<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Lock, Sparkles, Volume2 } from 'lucide-vue-next';

export type CatalogVoice = {
    id: string;
    name: string;
    description: string;
    tier: string;
    provider: string;
    preview_text: string;
    selectable: boolean;
};

defineProps<{
    voices: CatalogVoice[];
    modelValue: string;
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
</script>

<template>
    <div class="grid gap-3">
        <button
            v-for="voice in voices"
            :key="voice.id"
            type="button"
            class="rounded-3xl border-2 p-4 text-left transition"
            :class="[
                modelValue === voice.id && voice.selectable
                    ? 'border-orange-400 bg-orange-50 ring-4 ring-orange-200'
                    : 'border-sky-200 bg-white',
                voice.selectable ? 'hover:border-sky-300 hover:bg-sky-50' : 'cursor-not-allowed opacity-80',
            ]"
            @click="select(voice)"
        >
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-lg font-extrabold text-slate-800">{{ voice.name }}</h3>
                        <span
                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-extrabold"
                            :class="
                                voice.tier === 'premium'
                                    ? 'bg-amber-200 text-amber-900'
                                    : 'bg-emerald-200 text-emerald-900'
                            "
                        >
                            <Sparkles class="h-3 w-3" />
                            {{ voice.tier === 'premium' ? 'Premium' : 'Free' }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm font-semibold text-slate-600">{{ voice.description }}</p>
                    <p v-if="!voice.selectable" class="mt-2 text-xs font-extrabold uppercase tracking-wide text-rose-500">
                        Coming soon
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
                    :disabled="!voice.selectable"
                    @click.stop="emit('preview', voice)"
                >
                    <Volume2 class="mr-2 h-4 w-4" />
                    Preview
                </Button>
            </div>
        </button>
    </div>
</template>
