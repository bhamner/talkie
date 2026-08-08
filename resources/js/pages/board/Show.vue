<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useSpeech } from '@/composables/useSpeech';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BookOpen,
    Delete,
    FolderOpen,
    HandHeart,
    Heart,
    Home,
    MapPin,
    Smile,
    Sparkles,
    Users,
    Utensils,
    Volume2,
    Zap,
} from 'lucide-vue-next';
import { computed, ref, type Component, watch } from 'vue';

type BoardMenu = {
    id: number;
    name: string;
    parent_id: number | null;
    sort_order?: number;
};

type BoardWord = {
    id: number;
    label: string;
    speak_text: string;
};

type Ancestor = {
    id: number;
    name: string;
};

const props = defineProps<{
    menu: { id: number; name: string; parent_id: number | null } | null;
    menus: BoardMenu[];
    words: BoardWord[];
    ancestors: Ancestor[];
    is_guest: boolean;
    preferred_name: string | null;
    voice: { id: string | null; uri: string | null; name: string | null };
}>();

const phrase = ref<BoardWord[]>([]);
const { speak, selectedVoiceUri } = useSpeech(props.voice.uri);

watch(
    () => props.voice.uri,
    (uri) => {
        selectedVoiceUri.value = uri;
    },
);

const menuIcons: Record<string, Component> = {
    Food: Utensils,
    Drinks: Utensils,
    Feelings: Heart,
    People: Users,
    Places: MapPin,
    Actions: Zap,
};

const phraseText = computed(() => phrase.value.map((word) => word.speak_text).join(' '));
const showGreeting = computed(() => !props.is_guest && !props.menu && !!props.preferred_name);
const pageTitle = computed(() => props.menu?.name ?? 'Home');

const tileClass = (index: number) => `talkie-tile-${index % 6}`;

const menuIcon = (name: string) => menuIcons[name] ?? FolderOpen;

const addWord = (word: BoardWord) => {
    phrase.value.push(word);
};

const removeLast = () => {
    phrase.value.pop();
};

const clearPhrase = () => {
    phrase.value = [];
};

const speakPhrase = () => {
    if (phraseText.value) {
        speak(phraseText.value);
    }
};

const speakWord = (word: BoardWord) => {
    speak(word.speak_text);
};

const speakGreeting = () => {
    if (props.preferred_name) {
        speak(`Hello my name is ${props.preferred_name}`);
    }
};
</script>

<template>
    <Head :title="pageTitle" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wide text-sky-600">
                        {{ is_guest ? 'Guest board' : 'My board' }}
                    </p>
                    <h1 class="flex items-center gap-2 text-3xl font-extrabold tracking-tight text-slate-800 sm:text-4xl">
                        <component :is="menu ? menuIcon(menu.name) : Home" class="h-8 w-8 text-orange-500" />
                        {{ pageTitle }}
                    </h1>
                </div>

                <div v-if="ancestors.length" class="flex flex-wrap items-center gap-2 text-sm font-bold text-sky-700">
                    <Link :href="route('board')" class="inline-flex items-center gap-1 rounded-full bg-white/80 px-3 py-1 shadow-sm">
                        <Home class="h-3.5 w-3.5" />
                        Home
                    </Link>
                    <template v-for="ancestor in ancestors" :key="ancestor.id">
                        <span>/</span>
                        <Link :href="`/board/${ancestor.id}`" class="rounded-full bg-white/80 px-3 py-1 shadow-sm">
                            {{ ancestor.name }}
                        </Link>
                    </template>
                </div>
            </div>

            <button
                v-if="showGreeting"
                type="button"
                class="flex min-h-24 w-full items-center justify-center gap-3 rounded-3xl bg-gradient-to-r from-orange-400 via-rose-400 to-pink-400 px-4 py-5 text-center text-2xl font-extrabold text-white shadow-lg transition hover:scale-[1.01] active:scale-[0.99] sm:text-3xl"
                @click="speakGreeting"
            >
                <HandHeart class="h-8 w-8 shrink-0" />
                Hello, my name is {{ preferred_name }}!
            </button>

            <div class="rounded-3xl border-2 border-sky-200 bg-white/90 p-4 shadow-md backdrop-blur">
                <div class="mb-3 flex min-h-16 flex-wrap items-center gap-2">
                    <span
                        v-for="(word, index) in phrase"
                        :key="`${word.id}-${index}`"
                        class="rounded-full px-4 py-2 text-base font-extrabold shadow-sm"
                        :class="tileClass(index)"
                    >
                        {{ word.label }}
                    </span>
                    <span v-if="phrase.length === 0" class="inline-flex items-center gap-2 text-base font-semibold text-slate-500">
                        <Sparkles class="h-4 w-4 text-amber-500" />
                        Tap words to build a fun phrase
                    </span>
                </div>

                <div class="flex flex-wrap gap-2">
                    <Button
                        type="button"
                        class="h-12 rounded-full px-5 text-base font-extrabold shadow-md"
                        @click="speakPhrase"
                        :disabled="phrase.length === 0"
                    >
                        <Volume2 class="mr-2 h-5 w-5" />
                        Speak
                    </Button>
                    <Button
                        type="button"
                        variant="secondary"
                        class="h-12 rounded-full px-5 text-base font-extrabold"
                        @click="removeLast"
                        :disabled="phrase.length === 0"
                    >
                        <Delete class="mr-2 h-5 w-5" />
                        Oops
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        class="h-12 rounded-full px-5 text-base font-extrabold"
                        @click="clearPhrase"
                        :disabled="phrase.length === 0"
                    >
                        Clear
                    </Button>
                    <Button v-if="menu" type="button" variant="outline" class="h-12 rounded-full px-5 text-base font-extrabold" as-child>
                        <Link :href="menu.parent_id ? `/board/${menu.parent_id}` : '/board'">
                            <ArrowLeft class="mr-2 h-5 w-5" />
                            Back
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                <Link
                    v-for="child in menus"
                    :key="`menu-${child.id}`"
                    :href="`/board/${child.id}`"
                    class="talkie-folder flex min-h-28 flex-col items-center justify-center gap-2 rounded-3xl px-3 py-4 text-center text-xl font-extrabold transition active:scale-95"
                >
                    <component :is="menuIcon(child.name)" class="h-8 w-8" />
                    {{ child.name }}
                </Link>

                <button
                    v-for="(word, index) in words"
                    :key="`word-${word.id}`"
                    type="button"
                    class="flex min-h-28 flex-col items-center justify-center gap-2 rounded-3xl border-2 px-3 py-4 text-center text-xl font-extrabold shadow-sm transition active:scale-95"
                    :class="tileClass(index)"
                    @click="addWord(word)"
                    @dblclick="speakWord(word)"
                >
                    <Smile class="h-6 w-6 opacity-70" />
                    {{ word.label }}
                </button>
            </div>

            <p v-if="menus.length === 0 && words.length === 0" class="text-center text-base font-semibold text-slate-500">
                No words here yet — try another folder.
            </p>

            <p class="flex items-center justify-center gap-2 text-center text-sm font-semibold text-sky-700/80">
                <BookOpen class="h-4 w-4" />
                Tip: tap to add · double-tap to speak a word alone
            </p>
        </div>
    </AppLayout>
</template>
