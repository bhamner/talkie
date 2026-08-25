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

const { speak, selectedVoiceUri, voices: deviceVoices, isSupported, isPreparingVoice, voiceError } = useSpeech(
    props.voice.uri,
);

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
                    description="Friendly uses this device. Piper is a high quality neural TTS voice that downloads once after you pick it."
                />

                <div v-if="!isSupported" class="rounded-lg border border-destructive/40 bg-destructive/10 p-4 text-sm">
                    This browser cannot play device voices. You can still pick Piper.
                </div>

                <form class="space-y-6" @submit.prevent="submit">
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
                    <Button type="submit" :disabled="form.processing || isPreparingVoice">Save voice</Button>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
