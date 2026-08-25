<script setup lang="ts">
import VoiceCatalog, { type CatalogVoice } from '@/components/VoiceCatalog.vue';
import { Button } from '@/components/ui/button';
import { useSpeech } from '@/composables/useSpeech';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

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
    voice_id: props.voice.id ?? 'device-default',
    voice_uri: props.voice.uri,
    voice_name: props.voice.name,
});

const selectedCatalog = computed(() => props.voices.find((voice) => voice.id === form.voice_id));

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

const preview = async (voice: CatalogVoice) => {
    if (!voice.selectable) {
        return;
    }

    applySelection(voice);
    try {
        await speak(voice.preview_text, voice, { fallbackToDevice: false });
    } catch {
        // voiceError is shown next to the catalog
    }
};

const submit = () => {
    const selected = selectedCatalog.value;

    if (selected) {
        applySelection(selected);
    }

    form.put(route('onboarding.voice.update'));
};
</script>

<template>
    <AuthBase
        title="Choose your voice"
        :description="`Nice to meet you, ${preferred_name}. Pick the voice Talkie will use.`"
    >
        <Head title="Choose your voice" />

        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <p v-if="isPreparingVoice" class="text-sm font-semibold text-sky-800">
                Downloading Piper (~80 MB). This happens once, then previews should be quicker.
            </p>
            <p v-if="voiceError" class="text-sm font-semibold text-rose-700">{{ voiceError }}</p>
            <VoiceCatalog
                v-model="form.voice_id"
                :voices="voices"
                :previewing="isPreparingVoice"
                @preview="preview"
            />

            <Button
                type="submit"
                class="h-12 w-full rounded-full text-base font-extrabold"
                :disabled="form.processing || isPreparingVoice"
            >
                Save and start talking!
            </Button>
        </form>
    </AuthBase>
</template>
