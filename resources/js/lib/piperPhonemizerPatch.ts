const PHONEME_BLOCK = /const phonemeIds = await new Promise\(async \(resolve\) => \{[\s\S]*?"\/espeak-ng-data"\s*\]\);\s*\}\);/;

const QUEUE_WRAPPER = `predictChunk_fn = ((talkiePredictChunk) => async function (text) {
  return talkieWithPhonemizer(() => talkiePredictChunk.call(this, text));
})(predictChunk_fn);
`;

const HELPERS = `let talkiePhonemizer = null;
let talkiePhonemizerUses = 0;
let talkiePhonemePrint = (data) => {
};
const talkiePhonemePrintBridge = (data) => talkiePhonemePrint(data);
let talkiePhonemeQueue = Promise.resolve();
const talkieWithPhonemizer = (task) => {
  const run = talkiePhonemeQueue.then(task, task);
  talkiePhonemeQueue = run.then(() => {
  }, () => {
  });
  return run;
};
const PHONEMIZER_REUSE_LIMIT = 40;
`;

const PHONEME_REPLACEMENT = `const phonemeIds = await new Promise((resolve, reject) => {
    let settled = false;
    let buffer = "";
    const timeout = setTimeout(() => {
      fail(new Error("Piper phonemizer timed out"), true);
    }, 30000);
    const finish = (ids) => {
      if (settled) {
        return;
      }
      settled = true;
      clearTimeout(timeout);
      resolve(ids);
    };
    const fail = (error, resetModule) => {
      if (settled) {
        return;
      }
      settled = true;
      clearTimeout(timeout);
      if (resetModule) {
        talkiePhonemizer = null;
        talkiePhonemizerUses = 0;
      }
      reject(error);
    };
    const consume = (chunk, flush) => {
      buffer += String(chunk);
      const lines = buffer.split(/\\r?\\n/);
      buffer = flush ? "" : (lines.pop() ?? "");
      for (const line of lines) {
        const trimmed = line.trim();
        if (!trimmed) {
          continue;
        }
        try {
          const parsed = JSON.parse(trimmed);
          if (Array.isArray(parsed.phoneme_ids)) {
            if (parsed.phoneme_ids.length === 0) {
              fail(new Error("Piper phonemizer returned no phonemes"), false);
            } else {
              finish(parsed.phoneme_ids);
            }
          }
        } catch {
        }
      }
    };
    talkiePhonemePrint = (data) => consume(data, false);
    const locateFile = (url) => {
      if (url.endsWith(".wasm")) return __privateGet(this, _wasmPaths).piperWasm;
      if (url.endsWith(".data")) return __privateGet(this, _wasmPaths).piperData;
      return url;
    };
    const boot = async () => {
      if (!talkiePhonemizer || talkiePhonemizerUses >= PHONEMIZER_REUSE_LIMIT) {
        talkiePhonemizer = await __privateGet(this, _createPiperPhonemize).call(this, {
          print: talkiePhonemePrintBridge,
          printErr: () => {
          },
          locateFile,
          noExitRuntime: true
        });
        talkiePhonemizerUses = 0;
      }
      talkiePhonemizerUses += 1;
      talkiePhonemizer.callMain([
        "-l",
        __privateGet(this, _modelConfig).espeak.voice,
        "--input",
        input,
        "--espeak_data",
        "/espeak-ng-data"
      ]);
      consume("\\n", true);
      if (!settled) {
        fail(new Error("Piper phonemizer returned no phonemes"), false);
      }
    };
    boot().catch((error) => fail(error, true));
  });`;

export function patchPiperPhonemizerSource(code: string): string {
    if (code.includes('let talkiePhonemizer = null;')) {
        return code;
    }

    if (!PHONEME_BLOCK.test(code) || !code.includes('return pcm;\n};')) {
        throw new Error('Piper phonemizer patch no longer matches @mintplex-labs/piper-tts-web.');
    }

    return code
        .replace('predictChunk_fn = async function(text) {', `${HELPERS}predictChunk_fn = async function(text) {`)
        .replace(PHONEME_BLOCK, PHONEME_REPLACEMENT)
        .replace('return pcm;\n};', `return pcm;\n};\n${QUEUE_WRAPPER}`);
}
