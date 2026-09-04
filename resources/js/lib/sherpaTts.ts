import { setPiperProgress } from '@/lib/voiceProgress';
import { SherpaTts, type SherpaProgressEvent } from '@talkie/sherpa-tts';

let listening = false;

const ensureProgressListener = async (): Promise<void> => {
    if (listening) {
        return;
    }

    listening = true;
    await SherpaTts.addListener('progress', (event: SherpaProgressEvent) => {
        setPiperProgress(event.phase, event.loaded, event.total);
    });
};

export async function warmupSherpa(model: string): Promise<void> {
    await ensureProgressListener();
    await SherpaTts.warmup({ model });
}

export async function speakSherpa(text: string, model: string, speakerId = 0): Promise<void> {
    await ensureProgressListener();
    await SherpaTts.speak({ text, model, speakerId });
}

export async function cancelSherpa(): Promise<void> {
    await SherpaTts.cancel();
}
