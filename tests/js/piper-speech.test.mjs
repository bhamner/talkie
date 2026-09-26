import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { mkdtempSync, readFileSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import path from 'node:path';
import test from 'node:test';
import { pathToFileURL } from 'node:url';

const root = path.resolve(import.meta.dirname, '../..');
const outDir = mkdtempSync(path.join(tmpdir(), 'piper-speech-'));

const bundle = (entry) => {
    const outfile = path.join(outDir, `${path.basename(entry, '.ts')}.js`);

    execFileSync(path.join(root, 'node_modules/.bin/esbuild'), [entry, '--bundle', '--format=esm', `--outfile=${outfile}`], {
        cwd: root,
        stdio: 'inherit',
    });

    return import(pathToFileURL(outfile));
};

const wav = (samples, sampleRate = 22050) => {
    const dataBytes = samples.length * 2;
    const buffer = new ArrayBuffer(44 + dataBytes);
    const view = new DataView(buffer);

    view.setUint32(0, 0x46464952, true);
    view.setUint32(4, 36 + dataBytes, true);
    view.setUint32(8, 0x45564157, true);
    view.setUint32(12, 0x20746d66, true);
    view.setUint32(16, 16, true);
    view.setUint16(20, 1, true);
    view.setUint16(22, 1, true);
    view.setUint32(24, sampleRate, true);
    view.setUint32(28, sampleRate * 2, true);
    view.setUint16(32, 2, true);
    view.setUint16(34, 16, true);
    view.setUint32(36, 0x61746164, true);
    view.setUint32(40, dataBytes, true);

    samples.forEach((sample, index) => {
        const clamped = Math.max(-1, Math.min(1, sample));
        view.setInt16(44 + index * 2, clamped * 32767, true);
    });

    return buffer;
};

const duration = (buffer) => {
    const view = new DataView(buffer);

    return view.getUint32(40, true) / 2 / view.getUint32(24, true);
};

test('faint short words get the Piper lead-in and loud words do not', async () => {
    const { isFaintPiperWav, rescuePiperUtterance } = await bundle('resources/js/lib/piperUtterance.ts');

    assert.equal(rescuePiperUtterance('in'), '---in.');
    assert.equal(rescuePiperUtterance('  the '), '---the.');
    assert.equal(rescuePiperUtterance('in the house'), '---in the house');

    const whisper = wav(Array.from({ length: Math.round(22050 * 0.22) }, () => 0.03));
    const spoken = wav(Array.from({ length: Math.round(22050 * 0.45) }, () => 0.4));

    assert.equal(isFaintPiperWav(whisper), true);
    assert.equal(isFaintPiperWav(spoken), false);
});

test('short clips gain enough silence to survive playback', async () => {
    const { padPiperWav, wavPeak } = await bundle('resources/js/lib/piperUtterance.ts');
    const samples = Array.from({ length: Math.round(22050 * 0.05) }, () => 0.4);
    const clip = wav(samples);
    const padded = padPiperWav(clip);

    assert.ok(duration(padded) >= 0.35);
    assert.ok(wavPeak(padded) > 0.3);
    assert.equal(wavPeak(wav(Array.from({ length: 200 }, () => 0))), 0);

    const longClip = wav(Array.from({ length: 22050 }, () => 0.2));
    const paddedLong = padPiperWav(longClip);

    assert.ok(duration(paddedLong) > duration(longClip));
    assert.ok(duration(paddedLong) - duration(longClip) < 0.12);
});

test('piper keeps one phonemizer instead of building it for every word', async () => {
    const { patchPiperPhonemizerSource } = await bundle('resources/js/lib/piperPhonemizerPatch.ts');
    const source = readFileSync(path.join(root, 'node_modules/@mintplex-labs/piper-tts-web/dist/piper-tts-web.js'), 'utf8');
    const patched = patchPiperPhonemizerSource(source);
    const patchedFile = path.join(outDir, 'piper-tts-web.patched.js');

    assert.notEqual(patched, source);
    assert.match(patched, /let talkiePhonemizer = null;/);
    assert.match(patched, /talkiePhonemePrintBridge/);
    assert.match(patched, /talkieWithPhonemizer/);
    assert.equal(patched.includes('new Promise(async (resolve)'), false);
    assert.equal(patchPiperPhonemizerSource(patched), patched);

    writeFileSync(patchedFile, patched);
    execFileSync(process.execPath, ['--check', patchedFile], { stdio: 'inherit' });
});
