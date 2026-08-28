import type { PluginListenerHandle } from '@capacitor/core';

export type SherpaProgressPhase = 'idle' | 'preparing' | 'downloading';

export type SherpaProgressEvent = {
    phase: SherpaProgressPhase;
    loaded: number;
    total: number;
};

export type SherpaWarmupOptions = {
    model: string;
};

export type SherpaSpeakOptions = {
    text: string;
    model: string;
    speakerId?: number;
};

export interface SherpaTtsPlugin {
    warmup(options: SherpaWarmupOptions): Promise<void>;
    speak(options: SherpaSpeakOptions): Promise<void>;
    cancel(): Promise<void>;
    addListener(eventName: 'progress', listenerFunc: (event: SherpaProgressEvent) => void): Promise<PluginListenerHandle>;
    removeAllListeners(): Promise<void>;
}
