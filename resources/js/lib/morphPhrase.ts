export type MorphKind = 'plural' | 'ing' | 'ed' | 'ly' | 'possessive';

export type MorphWord = {
    label: string;
    speak_text?: string | null;
};

export type PhraseToken = {
    word: MorphWord;
    morph?: MorphKind;
};

export const MORPH_TILES: { kind: MorphKind; label: string }[] = [
    { kind: 'plural', label: 's' },
    { kind: 'ing', label: 'ing' },
    { kind: 'ed', label: 'ed' },
    { kind: 'ly', label: 'ly' },
    { kind: 'possessive', label: "'s" },
];

const pluralExceptions: Record<string, string> = {
    child: 'children',
    foot: 'feet',
    leaf: 'leaves',
    mouse: 'mice',
    person: 'people',
    tooth: 'teeth',
    man: 'men',
    woman: 'women',
};

const pastExceptions: Record<string, string> = {
    go: 'went',
    come: 'came',
    see: 'saw',
    say: 'said',
    get: 'got',
    make: 'made',
    find: 'found',
    run: 'ran',
    sit: 'sat',
    fall: 'fell',
    throw: 'threw',
    give: 'gave',
    know: 'knew',
    take: 'took',
    eat: 'ate',
};

const baseOf = (word: MorphWord): string => word.speak_text?.trim() || word.label;

const preserveCase = (base: string, result: string): string => {
    if (base.length === 0) {
        return result;
    }

    if (base === base.toUpperCase()) {
        return result.toUpperCase();
    }

    if (base[0] === base[0].toUpperCase() && base.slice(1) === base.slice(1).toLowerCase()) {
        return result.charAt(0).toUpperCase() + result.slice(1);
    }

    return result;
};

const endsWithConsonantY = (lower: string): boolean => /[^aeiou]y$/.test(lower);

const endsWithSibilant = (lower: string): boolean => /(?:s|ss|sh|ch|x|z)$/.test(lower);

const endsWithConsonantVowelConsonant = (lower: string): boolean => {
    if (lower.length < 3) {
        return false;
    }

    const last = lower.slice(-3);
    const [c1, v, c2] = last.split('');

    return /[^aeiou]/.test(c1) && /[aeiou]/.test(v) && /[^aeiouwxy]/.test(c2);
};

export function applyMorph(base: string, kind: MorphKind): string {
    const trimmed = base.trim();
    if (!trimmed) {
        return trimmed;
    }

    const lower = trimmed.toLowerCase();
    let result = trimmed;

    if (kind === 'plural') {
        if (pluralExceptions[lower]) {
            result = pluralExceptions[lower];
        } else if (endsWithConsonantY(lower)) {
            result = `${trimmed.slice(0, -1)}ies`;
        } else if (endsWithSibilant(lower) || lower.endsWith('o')) {
            result = `${trimmed}es`;
        } else if (lower.endsWith('f')) {
            result = `${trimmed.slice(0, -1)}ves`;
        } else if (lower.endsWith('fe')) {
            result = `${trimmed.slice(0, -2)}ves`;
        } else {
            result = `${trimmed}s`;
        }
    }

    if (kind === 'ing') {
        if (lower.endsWith('ie')) {
            result = `${trimmed.slice(0, -2)}ying`;
        } else if (lower.endsWith('e') && !lower.endsWith('ee')) {
            result = `${trimmed.slice(0, -1)}ing`;
        } else if (endsWithConsonantVowelConsonant(lower)) {
            result = `${trimmed}${trimmed.slice(-1)}ing`;
        } else {
            result = `${trimmed}ing`;
        }
    }

    if (kind === 'ed') {
        if (pastExceptions[lower]) {
            result = pastExceptions[lower];
        } else if (lower.endsWith('e')) {
            result = `${trimmed}d`;
        } else if (endsWithConsonantY(lower)) {
            result = `${trimmed.slice(0, -1)}ied`;
        } else if (endsWithConsonantVowelConsonant(lower)) {
            result = `${trimmed}${trimmed.slice(-1)}ed`;
        } else {
            result = `${trimmed}ed`;
        }
    }

    if (kind === 'ly') {
        if (endsWithConsonantY(lower)) {
            result = `${trimmed.slice(0, -1)}ily`;
        } else if (lower.endsWith('le')) {
            result = `${trimmed.slice(0, -1)}y`;
        } else if (lower.endsWith('ic')) {
            result = `${trimmed}ally`;
        } else {
            result = `${trimmed}ly`;
        }
    }

    if (kind === 'possessive') {
        if (lower.endsWith('s')) {
            result = `${trimmed}'`;
        } else {
            result = `${trimmed}'s`;
        }
    }

    return preserveCase(trimmed, result.toLowerCase() === result ? result : result);
}

export function displayLabel(token: PhraseToken): string {
    const base = token.word.label;
    if (!token.morph) {
        return base;
    }

    return applyMorph(base, token.morph);
}

export function speakText(token: PhraseToken): string {
    const base = baseOf(token.word);
    if (!token.morph) {
        return base;
    }

    return applyMorph(base, token.morph);
}

export function phraseSpeakText(tokens: PhraseToken[]): string {
    return tokens.map((token) => speakText(token)).join(' ').trim();
}

export function applyMorphToLast(tokens: PhraseToken[], kind: MorphKind): PhraseToken[] {
    if (tokens.length === 0) {
        return tokens;
    }

    const next = [...tokens];
    const last = next[next.length - 1];
    next[next.length - 1] = { ...last, morph: kind };

    return next;
}

export function removeLastPhrasePart(tokens: PhraseToken[]): PhraseToken[] {
    if (tokens.length === 0) {
        return tokens;
    }

    const last = tokens[tokens.length - 1];
    if (last.morph) {
        const next = [...tokens];
        next[next.length - 1] = { word: last.word };

        return next;
    }

    return tokens.slice(0, -1);
}
