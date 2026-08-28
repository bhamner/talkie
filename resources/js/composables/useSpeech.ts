import { cancelPiperPlayback, piperProgress, speakPiper, warmupPiper } from '@/lib/piperTts';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

export type DeviceVoice = {
    uri: string;
    name: string;
    lang: string;
    default: boolean;
};

export type SpeakCatalog = {
    provider?: string | null;
    engine?: string | null;
    model?: string | null;
    speaker_id?: number | null;
    speakerId?: number | null;
};

export type SpeakOptions = {
    fallbackToDevice?: boolean;
};

export function useSpeech(initialVoiceUri: string | null = null) {
    const page = usePage<SharedData>();
    const voices = ref<DeviceVoice[]>([]);
    const selectedVoiceUri = ref<string | null>(initialVoiceUri);
    const isSupported = ref(false);
    const voiceError = ref<string | null>(null);
    const isPreparingVoice = computed(() => piperProgress.phase === 'downloading' || piperProgress.phase === 'preparing');

    const loadVoices = () => {
        if (!('speechSynthesis' in window)) {
            isSupported.value = false;
            return;
        }

        isSupported.value = true;

        const deviceVoices = window.speechSynthesis.getVoices();
        voices.value = deviceVoices.map((voice) => ({
            uri: voice.voiceURI,
            name: voice.name,
            lang: voice.lang,
            default: voice.default,
        }));

        if (!selectedVoiceUri.value && voices.value.length > 0) {
            const preferred = voices.value.find((voice) => voice.default) ?? voices.value[0];
            selectedVoiceUri.value = preferred.uri;
        }
    };

    const speakDevice = (text: string) => {
        if (!('speechSynthesis' in window)) {
            return;
        }

        window.speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        const match = window.speechSynthesis.getVoices().find((voice) => voice.voiceURI === selectedVoiceUri.value);

        if (match) {
            utterance.voice = match;
        }

        window.speechSynthesis.speak(utterance);
    };

    const speak = async (text: string, catalog?: SpeakCatalog, options: SpeakOptions = {}): Promise<void> => {
        const trimmed = text.trim();
        if (!trimmed) {
            return;
        }

        const provider = catalog?.provider ?? page.props.voice?.provider ?? 'device';
        const engine = catalog?.engine ?? page.props.voice?.engine ?? null;
        const model = catalog?.model ?? page.props.voice?.model ?? null;
        const speakerId = catalog?.speakerId ?? catalog?.speaker_id ?? page.props.voice?.speaker_id ?? 0;
        const fallbackToDevice = options.fallbackToDevice ?? true;

        if (provider === 'bundled' && engine === 'piper' && model) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
            }

            voiceError.value = null;

            try {
                await speakPiper(trimmed, model, speakerId);
            } catch (error) {
                console.error('Piper voice failed', error);

                if (error instanceof DOMException && error.name === 'NotAllowedError') {
                    voiceError.value = 'Piper is ready. Try again to hear it.';
                } else {
                    voiceError.value = 'Piper could not play. Stay on this page — the first time downloads a large voice file.';
                }

                if (fallbackToDevice) {
                    speakDevice(trimmed);
                    return;
                }

                throw error;
            }

            return;
        }

        cancelPiperPlayback();
        speakDevice(trimmed);
    };

    onMounted(() => {
        loadVoices();

        if ('speechSynthesis' in window) {
            window.speechSynthesis.onvoiceschanged = loadVoices;
        }

        const current = page.props.voice;
        if (current?.engine === 'piper' && current.model) {
            void warmupPiper(current.model);
        }
    });

    return {
        voices,
        selectedVoiceUri,
        isSupported,
        isPreparingVoice,
        voiceError,
        speak,
        loadVoices,
    };
}
