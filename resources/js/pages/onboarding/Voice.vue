<script setup lang="ts">
import VoiceCatalog, { type CatalogVoice } from '@/components/VoiceCatalog.vue';
import VoiceProgressBanner from '@/components/VoiceProgressBanner.vue';
import { useSpeech } from '@/composables/useSpeech';
import { prefersPiperOnNative, warmupPiper } from '@/lib/piperTts';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    voices: CatalogVoice[];
    voice: {
        id: string | null;
        uri: string | null;
        name: string | null;
    };
    preferred_name: string;
}>();

const { speak, selectedVoiceUri, voices: deviceVoices, isPreparingVoice, voiceError } = useSpeech(props.voice.uri);

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

const chooseVoice = (voiceId: string) => {
    const voice = props.voices.find((item) => item.id === voiceId);

    if (!voice?.selectable || form.processing) {
        return;
    }

    applySelection(voice);

    if (voice.engine === 'piper' && voice.model) {
        void warmupPiper(voice.model);
    }

    form.put(route('onboarding.voice.update'));
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
    <AuthBase
        title="Choose your voice"
        :description="`Nice to meet you, ${preferred_name}. Pick the voice Talkie will use.`"
    >
        <Head title="Choose your voice" />

        <div class="flex flex-col gap-6">
            <VoiceProgressBanner />
            <p v-if="voiceError" class="text-sm font-semibold text-rose-700">{{ voiceError }}</p>
            <VoiceCatalog
                :model-value="form.voice_id"
                :voices="voices"
                :previewing="isPreparingVoice || form.processing"
                @preview="preview"
                @update:model-value="chooseVoice"
            />
        </div>
    </AuthBase>
</template>
