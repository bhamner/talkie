export type MathChallenge = {
    prompt: string;
    answer: number;
};

/** Simple addition adults can solve quickly; awkward for young kids. */
export function createMathChallenge(): MathChallenge {
    const left = randomInt(2, 9);
    const right = randomInt(2, 9);

    return {
        prompt: `${left} + ${right}`,
        answer: left + right,
    };
}

function randomInt(min: number, max: number): number {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}
