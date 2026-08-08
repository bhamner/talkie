import { onMounted, ref } from 'vue';

export type DeviceVoice = {
    uri: string;
    name: string;
    lang: string;
    default: boolean;
};

export function useSpeech(initialVoiceUri: string | null = null) {
    const voices = ref<DeviceVoice[]>([]);
    const selectedVoiceUri = ref<string | null>(initialVoiceUri);
    const isSupported = ref(false);

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
            const preferred =
                voices.value.find((voice) => voice.default) ?? voices.value[0];
            selectedVoiceUri.value = preferred.uri;
        }
    };

    const speak = (text: string) => {
        if (!text.trim() || !('speechSynthesis' in window)) {
            return;
        }

        window.speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        const match = window.speechSynthesis
            .getVoices()
            .find((voice) => voice.voiceURI === selectedVoiceUri.value);

        if (match) {
            utterance.voice = match;
        }

        window.speechSynthesis.speak(utterance);
    };

    onMounted(() => {
        loadVoices();

        if ('speechSynthesis' in window) {
            window.speechSynthesis.onvoiceschanged = loadVoices;
        }
    });

    return {
        voices,
        selectedVoiceUri,
        isSupported,
        speak,
        loadVoices,
    };
}
