<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import VoiceCatalog, { type CatalogVoice } from '@/components/VoiceCatalog.vue';
import { Button } from '@/components/ui/button';
import { useSpeech } from '@/composables/useSpeech';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

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

const { speak, selectedVoiceUri, voices: deviceVoices, isSupported } = useSpeech(props.voice.uri);

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
    form.put(route('voice.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Voice settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Voice"
                    description="Choose your speaking voice. Premium AI voices will unlock here later."
                />

                <div v-if="!isSupported" class="rounded-lg border border-destructive/40 bg-destructive/10 p-4 text-sm">
                    Speech synthesis is not supported in this browser.
                </div>

                <form v-else class="space-y-6" @submit.prevent="submit">
                    <VoiceCatalog v-model="form.voice_id" :voices="voices" @preview="preview" />
                    <Button type="submit" :disabled="form.processing">Save voice</Button>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
