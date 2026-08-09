<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useSpeech } from '@/composables/useSpeech';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { CaseSensitive, Delete, Keyboard, Space, Volume2, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const open = ref(false);
const draft = ref('');
const shift = ref(true);

const page = usePage<SharedData>();
const voiceUri = computed(() => page.props.voice?.uri ?? null);
const { speak, selectedVoiceUri } = useSpeech(voiceUri.value);

watch(voiceUri, (uri) => {
    selectedVoiceUri.value = uri;
});

const letters = 'abcdefghijklmnopqrstuvwxyz'.split('');

const displayLetter = (letter: string) => (shift.value ? letter.toUpperCase() : letter);

const append = (value: string) => {
    draft.value += value;
    if (shift.value && value !== ' ') {
        shift.value = false;
    }
};

const appendLetter = (letter: string) => {
    append(displayLetter(letter));
};

const backspace = () => {
    draft.value = draft.value.slice(0, -1);
};

const clearDraft = () => {
    draft.value = '';
    shift.value = true;
};

const speakDraft = () => {
    if (draft.value.trim()) {
        speak(draft.value.trim());
    }
};

const close = () => {
    open.value = false;
};
</script>

<template>
    <Teleport to="body">
        <Button
            v-if="!open"
            type="button"
            class="fixed bottom-4 right-4 z-[190] h-14 rounded-full px-5 text-base font-extrabold shadow-lg sm:bottom-6 sm:right-6"
            :aria-expanded="open"
            aria-controls="talkie-typing-keyboard"
            @click="open = true"
        >
            <Keyboard class="mr-2 h-5 w-5" />
            ABC
        </Button>

        <div
            v-if="open"
            class="fixed inset-0 z-[200] flex items-stretch justify-center bg-sky-950/40 p-3 sm:p-5"
            @click.self="close"
        >
            <div
                id="talkie-typing-keyboard"
                class="flex h-full w-full max-w-5xl flex-col overflow-hidden rounded-3xl border-2 border-sky-200 bg-white shadow-2xl"
                role="dialog"
                aria-modal="true"
                aria-label="Typing keyboard"
            >
                <div class="flex shrink-0 items-start justify-between gap-3 border-b border-sky-100 px-4 py-4 sm:px-6">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold uppercase tracking-wide text-sky-600">ABC</p>
                        <p class="mt-2 min-h-16 break-words text-2xl font-extrabold text-slate-800 sm:min-h-20 sm:text-3xl">
                            <span v-if="draft">{{ draft }}</span>
                            <span v-else class="font-semibold text-slate-400">Tap letters to write…</span>
                        </p>
                    </div>
                    <Button type="button" variant="ghost" size="icon" class="shrink-0 rounded-full" @click="close">
                        <X class="h-5 w-5" />
                        <span class="sr-only">Close keyboard</span>
                    </Button>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2 border-b border-sky-100 px-4 py-3 sm:px-6">
                    <Button
                        type="button"
                        class="h-12 rounded-full px-5 text-base font-extrabold shadow-md"
                        :disabled="!draft.trim()"
                        @click="speakDraft"
                    >
                        <Volume2 class="mr-2 h-5 w-5" />
                        Speak
                    </Button>
                    <Button
                        type="button"
                        variant="secondary"
                        class="h-12 rounded-full px-5 text-base font-extrabold"
                        :disabled="draft.length === 0"
                        @click="backspace"
                    >
                        <Delete class="mr-2 h-5 w-5" />
                        Oops
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        class="h-12 rounded-full px-5 text-base font-extrabold"
                        :disabled="draft.length === 0"
                        @click="clearDraft"
                    >
                        Clear
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        class="h-12 rounded-full px-5 text-base font-extrabold"
                        :class="shift ? 'border-orange-400 bg-orange-50 text-orange-700' : ''"
                        @click="shift = !shift"
                    >
                        <CaseSensitive class="mr-2 h-5 w-5" />
                        {{ shift ? 'ABC' : 'abc' }}
                    </Button>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4 sm:px-6 sm:py-5">
                    <div class="grid h-full min-h-[20rem] grid-cols-6 content-start gap-2 sm:grid-cols-9 sm:gap-3">
                        <button
                            v-for="letter in letters"
                            :key="letter"
                            type="button"
                            class="flex min-h-14 items-center justify-center rounded-2xl border-2 border-sky-100 bg-sky-50 text-2xl font-extrabold text-sky-900 shadow-sm transition hover:bg-sky-100 active:scale-95 sm:min-h-16 sm:text-3xl"
                            @click="appendLetter(letter)"
                        >
                            {{ displayLetter(letter) }}
                        </button>
                        <button
                            type="button"
                            class="col-span-3 flex min-h-14 items-center justify-center gap-2 rounded-2xl border-2 border-violet-100 bg-violet-50 text-xl font-extrabold text-violet-900 shadow-sm transition hover:bg-violet-100 active:scale-95 sm:col-span-4 sm:min-h-16 sm:text-2xl"
                            @click="append(' ')"
                        >
                            <Space class="h-6 w-6" />
                            Space
                        </button>
                        <button
                            type="button"
                            class="flex min-h-14 items-center justify-center rounded-2xl border-2 border-amber-100 bg-amber-50 text-2xl font-extrabold text-amber-900 shadow-sm transition hover:bg-amber-100 active:scale-95 sm:min-h-16 sm:text-3xl"
                            @click="append('.')"
                        >
                            .
                        </button>
                        <button
                            type="button"
                            class="flex min-h-14 items-center justify-center rounded-2xl border-2 border-amber-100 bg-amber-50 text-2xl font-extrabold text-amber-900 shadow-sm transition hover:bg-amber-100 active:scale-95 sm:min-h-16 sm:text-3xl"
                            @click="append('!')"
                        >
                            !
                        </button>
                        <button
                            type="button"
                            class="flex min-h-14 items-center justify-center rounded-2xl border-2 border-amber-100 bg-amber-50 text-2xl font-extrabold text-amber-900 shadow-sm transition hover:bg-amber-100 active:scale-95 sm:min-h-16 sm:text-3xl"
                            @click="append('?')"
                        >
                            ?
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
