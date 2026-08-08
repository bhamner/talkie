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

const { speak, selectedVoiceUri, voices: deviceVoices } = useSpeech(props.voice.uri);

const form = useForm({
    voice_id: props.voice.id ?? 'device-default',
    voice_uri: props.voice.uri,
    voice_name: props.voice.name,
});

const selectedCatalog = computed(() => props.voices.find((voice) => voice.id === form.voice_id));

const resolveDeviceVoice = () => {
    const preferred =
        deviceVoices.value.find((voice) => voice.default) ?? deviceVoices.value[0] ?? null;

    form.voice_uri = preferred?.uri ?? null;
    form.voice_name = selectedCatalog.value?.name ?? preferred?.name ?? null;
    selectedVoiceUri.value = preferred?.uri ?? null;
};

const preview = (voice: CatalogVoice) => {
    if (!voice.selectable) {
        return;
    }

    form.voice_id = voice.id;
    resolveDeviceVoice();
    speak(voice.preview_text);
};

const submit = () => {
    resolveDeviceVoice();
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
            <VoiceCatalog v-model="form.voice_id" :voices="voices" @preview="preview" />

            <Button type="submit" class="h-12 w-full rounded-full text-base font-extrabold" :disabled="form.processing">
                Save and start talking!
            </Button>
        </form>
    </AuthBase>
</template>
