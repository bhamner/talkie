import { reactive, readonly } from 'vue';

export type PiperPhase = 'idle' | 'preparing' | 'downloading';

type PiperProgressState = {
    phase: PiperPhase;
    loaded: number;
    total: number;
};

const progressState = reactive<PiperProgressState>({
    phase: 'idle',
    loaded: 0,
    total: 0,
});

export const piperProgress = readonly(progressState);

export function setPiperProgress(phase: PiperPhase, loaded = 0, total = 0): void {
    progressState.phase = phase;
    progressState.loaded = loaded;
    progressState.total = total;
}
