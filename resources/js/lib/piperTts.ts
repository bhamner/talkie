import { TtsSession, type VoiceId } from '@mintplex-labs/piper-tts-web';

export const LIBRITTS_VOICE_ID = 'en_US-libritts_r-medium';

const ONNX_WASM_BASE = 'https://cdnjs.cloudflare.com/ajax/libs/onnxruntime-web/1.18.0/';
const PIPER_WASM_BASE = 'https://cdn.jsdelivr.net/npm/@diffusionstudio/piper-wasm@1.0.0/build/piper_phonemize';

let sessionPromise: Promise<TtsSession> | null = null;
let currentAudio: HTMLAudioElement | null = null;
let onnxCreatePatched = false;

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
        sessionPromise = withSingleWasmThread(async () => {
            await patchOnnxToSingleThread();

            return TtsSession.create({
                voiceId,
                wasmPaths: {
                    onnxWasm: ONNX_WASM_BASE,
                    piperData: `${PIPER_WASM_BASE}.data`,
                    piperWasm: `${PIPER_WASM_BASE}.wasm`,
                },
                logger: import.meta.env.DEV ? (text: string) => console.info('[piper]', text) : undefined,
            });
        }).catch((error: unknown) => {
            sessionPromise = null;
            throw error;
        });
    }

    return sessionPromise;
};

export async function speakPiper(text: string, voiceId: string = LIBRITTS_VOICE_ID): Promise<void> {
    cancelPiperPlayback();

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
}
