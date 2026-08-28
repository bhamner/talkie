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
let currentAudio: HTMLAudioElement | null = null;
let onnxCreatePatched = false;

export const usesNativePiper = (): boolean => Capacitor.isNativePlatform() && Capacitor.getPlatform() === 'android';

const markIdleIfQuiet = (): void => {
    if (inflightSpeaks === 0 && sessionReady) {
        setPiperProgress('idle');
    }
};

const markWaiting = (): void => {
    if (piperProgress.phase === 'idle') {
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

    currentAudio?.pause();
    currentAudio = null;
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
        const { warmupSherpa } = await import('@/lib/sherpaTts');
        await warmupSherpa(voiceId);
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
    inflightSpeaks += 1;

    if (!sessionReady) {
        markWaiting();
    }

    try {
        const session = await sessionFor(voiceId as VoiceId);
        const wav = await session.predict(text);
        const url = URL.createObjectURL(wav);
        const audio = new Audio(url);

        currentAudio = audio;
        audio.addEventListener(
            'ended',
            () => {
                URL.revokeObjectURL(url);
                if (currentAudio === audio) {
                    currentAudio = null;
                }
            },
            { once: true },
        );

        try {
            await audio.play();
        } catch (error) {
            URL.revokeObjectURL(url);
            if (currentAudio === audio) {
                currentAudio = null;
            }

            throw error;
        }
    } finally {
        inflightSpeaks -= 1;
        markIdleIfQuiet();
    }
}
