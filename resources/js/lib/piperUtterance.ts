const FAINT_PEAK = 0.2;
const FAINT_SECONDS = 0.28;

/**
 * en_US-libritts_r whispers some short words ("in" is about a tenth as loud as
 * "hello"). Three hyphens are the Piper workaround that brings the word back.
 */
export function rescuePiperUtterance(text: string): string {
    const trimmed = text.trim().replace(/\s+/g, ' ');
    const match = trimmed.match(/^([A-Za-z']+)[.!?]?$/);

    if (match) {
        return `---${match[1]}.`;
    }

    return `---${trimmed}`;
}

const RIFF = 0x46464952;
const WAVE = 0x45564157;
const FMT = 0x20746d66;
const DATA = 0x61746164;

const readPcmWav = (
    wav: ArrayBuffer,
): {
    sampleRate: number;
    channels: number;
    data: Uint8Array;
} | null => {
    if (wav.byteLength < 44) {
        return null;
    }

    const view = new DataView(wav);

    if (view.getUint32(0, true) !== RIFF || view.getUint32(8, true) !== WAVE) {
        return null;
    }

    if (view.getUint32(12, true) !== FMT || view.getUint16(20, true) !== 1) {
        return null;
    }

    const channels = view.getUint16(22, true);
    const sampleRate = view.getUint32(24, true);
    const bits = view.getUint16(34, true);

    if (view.getUint32(36, true) !== DATA || bits !== 16 || channels < 1 || sampleRate < 1) {
        return null;
    }

    const dataBytes = view.getUint32(40, true);
    const available = Math.min(dataBytes, wav.byteLength - 44);

    if (available < 2) {
        return null;
    }

    return {
        sampleRate,
        channels,
        data: new Uint8Array(wav, 44, available),
    };
};

export function wavPeak(wav: ArrayBuffer): number {
    const pcm = readPcmWav(wav);

    if (!pcm) {
        return 0;
    }

    const view = new DataView(pcm.data.buffer, pcm.data.byteOffset, pcm.data.byteLength);
    let peak = 0;

    for (let index = 0; index + 1 < pcm.data.byteLength; index += 2) {
        peak = Math.max(peak, Math.abs(view.getInt16(index, true)));
    }

    return peak / 32768;
}

export function wavDurationSeconds(wav: ArrayBuffer): number {
    const pcm = readPcmWav(wav);

    if (!pcm) {
        return 0;
    }

    const frames = Math.floor(pcm.data.byteLength / (pcm.channels * 2));

    return frames / pcm.sampleRate;
}

export function isFaintPiperWav(wav: ArrayBuffer): boolean {
    return wavPeak(wav) < FAINT_PEAK || wavDurationSeconds(wav) < FAINT_SECONDS;
}

/**
 * Keep a short clip in the speaker buffer. Leading silence is only added when
 * the whole word would otherwise be shorter than the device can play.
 */
export function padPiperWav(wav: ArrayBuffer): ArrayBuffer {
    const pcm = readPcmWav(wav);

    if (!pcm) {
        return wav;
    }

    const bytesPerFrame = pcm.channels * 2;
    const frames = Math.floor(pcm.data.byteLength / bytesPerFrame);
    const duration = frames / pcm.sampleRate;
    const leadSeconds = duration < 0.3 ? 0.05 : 0;
    const tailSeconds = duration < 0.3 ? Math.max(0.2, 0.35 - duration) : 0.08;
    const leadBytes = Math.round(leadSeconds * pcm.sampleRate) * bytesPerFrame;
    const tailBytes = Math.round(tailSeconds * pcm.sampleRate) * bytesPerFrame;
    const out = new ArrayBuffer(44 + leadBytes + pcm.data.byteLength + tailBytes);
    const bytes = new Uint8Array(out);

    bytes.set(new Uint8Array(wav, 0, 44), 0);
    bytes.set(pcm.data, 44 + leadBytes);

    const header = new DataView(out);
    const dataBytes = leadBytes + pcm.data.byteLength + tailBytes;
    header.setUint32(4, 36 + dataBytes, true);
    header.setUint32(40, dataBytes, true);

    return out;
}
