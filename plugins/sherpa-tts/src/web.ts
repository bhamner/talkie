import { WebPlugin } from '@capacitor/core';
import type { SherpaTtsPlugin } from './definitions';

export class SherpaTtsWeb extends WebPlugin implements SherpaTtsPlugin {
    async warmup(): Promise<void> {
        throw this.unimplemented('Sherpa TTS is only available in the Android app.');
    }

    async speak(): Promise<void> {
        throw this.unimplemented('Sherpa TTS is only available in the Android app.');
    }

    async cancel(): Promise<void> {
        return;
    }
}
