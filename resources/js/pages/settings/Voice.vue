<script setup lang="ts">
import { TransitionRoot } from '@headlessui/vue';
import VoiceCatalog, { type CatalogVoice } from '@/components/VoiceCatalog.vue';
import { useSpeech } from '@/composables/useSpeech';
import { prefersPiperOnNative, warmupPiper } from '@/lib/piperTts';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    voices: CatalogVoice[];
    voice: {
        id: string | null;
        uri: string | null;
        name: string | null;
    };
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Voice settings',
        href: '/settings/voice',
    },
];

const { speak, selectedVoiceUri, voices: deviceVoices, isSupported, isPreparingVoice, voiceError } = useSpeech(
    props.voice.uri,
);

const form = useForm({
    voice_id: prefersPiperOnNative(props.voice.id) ? 'premium-nova' : (props.voice.id ?? 'device-default'),
    voice_uri: props.voice.uri,
    voice_name: prefersPiperOnNative(props.voice.id) ? (props.voice.name ?? 'Piper') : props.voice.name,
});

const applySelection = (voice: CatalogVoice) => {
    form.voice_id = voice.id;
    form.voice_name = voice.name;

    if (voice.provider === 'device') {
        const preferred =
            deviceVoices.value.find((deviceVoice) => deviceVoice.default) ?? deviceVoices.value[0] ?? null;

        form.voice_uri = preferred?.uri ?? null;
        selectedVoiceUri.value = preferred?.uri ?? null;
        return;
    }

    form.voice_uri = null;
};

const saveVoice = (voiceId: string) => {
    const voice = props.voices.find((item) => item.id === voiceId);

    if (!voice?.selectable || form.processing) {
        return;
    }

    applySelection(voice);

    if (voice.engine === 'piper' && voice.model) {
        void warmupPiper(voice.model);
    }

    if (voice.id === (props.voice.id ?? 'device-default')) {
        return;
    }

    form.put(route('voice.update'), {
        preserveScroll: true,
    });
};

const preview = async (voice: CatalogVoice) => {
    if (!voice.selectable) {
        return;
    }

    try {
        await speak(voice.preview_text, voice, { fallbackToDevice: false });
    } catch {
        // voiceError is shown next to the catalog
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Voice settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <div v-if="!isSupported" class="rounded-lg border border-destructive/40 bg-destructive/10 p-4 text-sm">
                    This browser cannot play device voices. You can still pick Piper.
                </div>

                <div class="space-y-6">
                    <p v-if="voiceError" class="text-sm font-semibold text-rose-700">{{ voiceError }}</p>
                    <VoiceCatalog
                        :model-value="form.voice_id"
                        :voices="voices"
                        :previewing="isPreparingVoice || form.processing"
                        @preview="preview"
                        @update:model-value="saveVoice"
                    />
                    <TransitionRoot
                        :show="form.recentlySuccessful"
                        enter="transition ease-in-out"
                        enter-from="opacity-0"
                        leave="transition ease-in-out"
                        leave-to="opacity-0"
                    >
                        <p class="text-sm font-semibold text-emerald-700">Saved</p>
                    </TransitionRoot>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
