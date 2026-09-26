import { isFaintPiperWav, padPiperWav, rescuePiperUtterance, wavPeak } from '@/lib/piperUtterance';
import { piperProgress, setPiperProgress } from '@/lib/voiceProgress';
import { Capacitor } from '@capacitor/core';
import { TtsSession, type Progress, type VoiceId } from '@mintplex-labs/piper-tts-web';

export const LIBRITTS_VOICE_ID = 'en_US-libritts_r-medium';
export type { PiperPhase } from '@/lib/voiceProgress';
export { piperProgress };

const ONNX_WASM_BASE = 'https://cdnjs.cloudflare.com/ajax/libs/onnxruntime-web/1.18.0/';
const PIPER_WASM_BASE = 'https://cdn.jsdelivr.net/npm/@diffusionstudio/piper-wasm@1.0.0/build/piper_phonemize';


let sessionPromise: Promise<TtsSession> | null = null;
let sessionReady = false;
let inflightSpeaks = 0;
let playbackToken = 0;
let currentAudio: HTMLAudioElement | null = null;
let sharedAudio: HTMLAudioElement | null = null;
let sharedAudioUrl: string | null = null;
let onnxCreatePatched = false;

export const usesNativePiper = (): boolean => Capacitor.isNativePlatform() && Capacitor.getPlatform() === 'android';

export const prefersPiperOnNative = (savedVoiceId: string | null | undefined): boolean =>
    Capacitor.isNativePlatform() && (savedVoiceId == null || savedVoiceId === '');

let nativeWarmed = false;

const markIdleIfQuiet = (): void => {
    if (inflightSpeaks === 0 && sessionReady) {
        setPiperProgress('idle');
    }
};

const markWaiting = (): void => {
    if (usesNativePiper() && piperProgress.phase === 'idle') {
        setPiperProgress('preparing');
    }
};

const onDownloadProgress = (progress: Progress): void => {
    if (progress.url.startsWith('tts://')) {
        return;
    }

    setPiperProgress('downloading', progress.loaded, progress.total);
};

type OnnxWasmRuntime = {
    env?: {
        wasm?: {
            numThreads?: number;
            wasmPaths?: string;
        };
    };
    InferenceSession?: {
        create: (...args: unknown[]) => Promise<unknown>;
    };
};

export function cancelPiperPlayback(): void {
    if (usesNativePiper()) {
        void import('@/lib/sherpaTts').then(({ cancelSherpa }) => cancelSherpa());
        return;
    }

    playbackToken += 1;
    currentAudio?.pause();
    currentAudio = null;

    if (sharedAudioUrl) {
        URL.revokeObjectURL(sharedAudioUrl);
        sharedAudioUrl = null;
    }
}

/**
 * Piper sets onnxruntime-web to navigator.hardwareConcurrency threads, which
 * needs Cross-Origin Isolation. Force one thread so it runs on a normal page.
 */
const withSingleWasmThread = async <T>(run: () => Promise<T>): Promise<T> => {
    const restorers: Array<() => void> = [];

    for (const target of [navigator, Navigator.prototype] as object[]) {
        const descriptor = Object.getOwnPropertyDescriptor(target, 'hardwareConcurrency');

        try {
            Object.defineProperty(target, 'hardwareConcurrency', {
                configurable: true,
                enumerable: true,
                get: () => 1,
            });
            restorers.push(() => {
                if (descriptor) {
                    Object.defineProperty(target, 'hardwareConcurrency', descriptor);
                } else {
                    Reflect.deleteProperty(target, 'hardwareConcurrency');
                }
            });
        } catch {
            // Safari can refuse to redefine navigator.hardwareConcurrency.
        }
    }

    try {
        return await run();
    } finally {
        restorers.reverse().forEach((restore) => restore());
    }
};

const patchOnnxToSingleThread = async (): Promise<void> => {
    const ortModule = await import('onnxruntime-web/wasm');
    const ort = ((ortModule as { default?: OnnxWasmRuntime }).default ?? ortModule) as OnnxWasmRuntime;

    if (ort.env?.wasm) {
        ort.env.wasm.numThreads = 1;
        ort.env.wasm.wasmPaths = ONNX_WASM_BASE;
    }

    if (!ort.InferenceSession || onnxCreatePatched) {
        return;
    }

    const originalCreate = ort.InferenceSession.create.bind(ort.InferenceSession);
    ort.InferenceSession.create = (...args: unknown[]) => {
        if (ort.env?.wasm) {
            ort.env.wasm.numThreads = 1;
        }

        return originalCreate(...args);
    };
    onnxCreatePatched = true;
};

const sessionFor = (voiceId: VoiceId): Promise<TtsSession> => {
    if (!sessionPromise) {
        markWaiting();
        sessionPromise = withSingleWasmThread(async () => {
            await patchOnnxToSingleThread();

            const session = await TtsSession.create({
                voiceId,
                progress: onDownloadProgress,
                wasmPaths: {
                    onnxWasm: ONNX_WASM_BASE,
                    piperData: `${PIPER_WASM_BASE}.data`,
                    piperWasm: `${PIPER_WASM_BASE}.wasm`,
                },
                logger: import.meta.env.DEV ? (text: string) => console.info('[piper]', text) : undefined,
            });

            sessionReady = true;

            if (inflightSpeaks === 0) {
                markIdleIfQuiet();
            } else if (piperProgress.phase === 'downloading') {
                setPiperProgress('preparing', piperProgress.loaded, piperProgress.total);
            }

            return session;
        }).catch((error: unknown) => {
            sessionPromise = null;
            sessionReady = false;
            setPiperProgress('idle');
            throw error;
        });
    }

    return sessionPromise;
};

export async function warmupPiper(voiceId: string = LIBRITTS_VOICE_ID): Promise<void> {
    if (usesNativePiper()) {
        if (nativeWarmed) {
            return;
        }

        const { warmupSherpa } = await import('@/lib/sherpaTts');
        await warmupSherpa(voiceId);
        nativeWarmed = true;
        return;
    }

    if (sessionReady) {
        return;
    }

    await sessionFor(voiceId as VoiceId);
}

export async function speakPiper(text: string, voiceId: string = LIBRITTS_VOICE_ID, speakerId = 0): Promise<void> {
    if (usesNativePiper()) {
        const { cancelSherpa, speakSherpa } = await import('@/lib/sherpaTts');
        await cancelSherpa();
        await speakSherpa(text, voiceId, speakerId);
        return;
    }

    cancelPiperPlayback();
    const token = playbackToken;
    inflightSpeaks += 1;

    if (!sessionReady) {
        markWaiting();
    }

    try {
        const session = await sessionFor(voiceId as VoiceId);
        const wav = await synthesizePiperWav(session, text, token);

        if (!wav || token !== playbackToken) {
            return;
        }

        await playPiperWav(wav, token);
    } finally {
        inflightSpeaks -= 1;
        markIdleIfQuiet();
    }
}

const canRetryPiper = (error: unknown): boolean => error instanceof Error && error.message.includes('no phonemes');

const synthesizePiperWav = async (session: TtsSession, text: string, token: number): Promise<ArrayBuffer | null> => {
    const plain = text.trim();
    const rescued = rescuePiperUtterance(plain);
    const attempts = rescued === plain ? [plain] : [plain, rescued];
    let last: ArrayBuffer | null = null;

    for (const [index, attempt] of attempts.entries()) {
        if (token !== playbackToken) {
            return null;
        }

        try {
            const wav = await session.predict(attempt);

            if (token !== playbackToken) {
                return null;
            }

            last = await wav.arrayBuffer();

            if (!isFaintPiperWav(last)) {
                return last;
            }
        } catch (error) {
            if (!canRetryPiper(error) || index === attempts.length - 1) {
                throw error;
            }
        }
    }

    if (!last || wavPeak(last) < 0.02) {
        throw new Error('Piper produced silence');
    }

    return last;
};

const playPiperWav = async (wav: ArrayBuffer, token: number): Promise<void> => {
    if (token !== playbackToken) {
        return;
    }

    const url = URL.createObjectURL(new Blob([padPiperWav(wav)], { type: 'audio/wav' }));
    const previousUrl = sharedAudioUrl;
    sharedAudioUrl = url;

    if (previousUrl) {
        URL.revokeObjectURL(previousUrl);
    }

    const audio = sharedAudio ?? new Audio();
    sharedAudio = audio;
    currentAudio = audio;
    audio.src = url;

    try {
        await audio.play();
    } catch (error) {
        if (sharedAudioUrl === url) {
            URL.revokeObjectURL(url);
            sharedAudioUrl = null;
        }

        if (currentAudio === audio) {
            currentAudio = null;
        }

        throw error;
    }

    if (token !== playbackToken) {
        audio.pause();
    }
};
