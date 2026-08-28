<script setup lang="ts">
import BoardMenuDialog from '@/components/BoardMenuDialog.vue';
import BoardMenuIcon from '@/components/BoardMenuIcon.vue';
import BoardWordDialog from '@/components/BoardWordDialog.vue';
import BoardWordIcon from '@/components/BoardWordIcon.vue';
import PhrasesPanel from '@/components/PhrasesPanel.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useBoardEditMode } from '@/composables/useBoardEditMode';
import { useSpeech } from '@/composables/useSpeech';
import AppLayout from '@/layouts/AppLayout.vue';
import { boardWordColor } from '@/lib/boardWordIcons';
import {
    applyMorphToLast,
    displayLabel,
    MORPH_TILES,
    phraseSpeakText,
    removeLastPhrasePart,
    type MorphKind,
    type PhraseToken,
} from '@/lib/morphPhrase';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    BookOpen,
    Check,
    Delete,
    Eye,
    EyeOff,
    FolderPlus,
    Home,
    Pencil,
    Plus,
    Search,
    Sparkles,
    Trash2,
    Volume2,
    X,
} from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

type BoardMenu = {
    id: number;
    name: string;
    slug: string;
    parent_id: number | null;
    icon?: string | null;
    sort_order?: number;
    is_builtin?: boolean;
    is_hidden?: boolean;
};

type BoardWord = {
    id: number;
    label: string;
    icon?: string | null;
    speak_text: string | null;
    is_builtin?: boolean;
    is_hidden?: boolean;
};

type BoardPhrase = {
    id: number | string;
    text: string;
    is_greeting?: boolean;
    is_builtin?: boolean;
    is_hidden?: boolean;
};

type Ancestor = {
    id: number;
    name: string;
    slug: string;
};

type SearchIndex = {
    menus: { id: number; name: string; slug: string; parent_id: number | null }[];
    words: { id: number; label: string; menu_id: number | null; menu_name: string; menu_slug: string | null }[];
};

const props = defineProps<{
    menu: { id: number; name: string; slug: string; parent_id: number | null; icon?: string | null } | null;
    menus: BoardMenu[];
    words: BoardWord[];
    phrases: BoardPhrase[];
    ancestors: Ancestor[];
    is_guest: boolean;
    can_edit: boolean;
    preferred_name: string | null;
    voice: {
        id: string | null;
        uri: string | null;
        name: string | null;
        provider?: string | null;
        engine?: string | null;
        model?: string | null;
    };
    highlight?: string | null;
    search_index: SearchIndex;
}>();

const DOUBLE_TAP_MS = 280;
const LONG_PRESS_MS = 500;

const phrase = ref<PhraseToken[]>([]);
const { editMode, exitEditMode } = useBoardEditMode();
const wordDialogOpen = ref(false);
const menuDialogOpen = ref(false);
const editingWord = ref<BoardWord | null>(null);
const editingMenu = ref<BoardMenu | null>(null);
const { speak, selectedVoiceUri } = useSpeech(props.voice.uri);

watch(
    () => props.voice.uri,
    (uri) => {
        selectedVoiceUri.value = uri;
    },
);

const textToSpeak = (word: BoardWord) => word.speak_text?.trim() || word.label;

const phraseText = computed(() => phraseSpeakText(phrase.value));
const pageTitle = computed(() => props.menu?.name ?? '');
const isNestedBoard = computed(() => props.menu !== null);
const boardUrl = (slug: string | null, highlight?: string): string => {
    const path = slug ? route('board', { menu: slug }) : route('board');

    return highlight ? `${path}?highlight=${encodeURIComponent(highlight)}` : path;
};
const backHref = computed(() => {
    if (props.ancestors.length < 2) {
        return route('board');
    }

    return boardUrl(props.ancestors[props.ancestors.length - 2].slug);
});

watch(
    isNestedBoard,
    (nested) => {
        document.body.classList.toggle('talkie-nested-board', nested);
    },
    { immediate: true },
);

const playableWords = computed(() => props.words.filter((word) => !word.is_hidden));
const playableMenus = computed(() => props.menus.filter((child) => !child.is_hidden));

const searchQuery = ref('');
const activeHighlight = ref(props.highlight?.trim() || '');

const normalizedSearch = computed(() => searchQuery.value.trim().toLowerCase());
const searchIsActive = computed(() => normalizedSearch.value.length > 0);

const matchesSearch = (value: string): boolean => {
    if (!searchIsActive.value) {
        return false;
    }

    return value.trim().toLowerCase().includes(normalizedSearch.value);
};

const isHighlightedMenu = (menuId: number, name: string): boolean => {
    if (activeHighlight.value === `menu-${menuId}`) {
        return true;
    }

    return matchesSearch(name);
};

const isHighlightedWord = (wordId: number, label: string): boolean => {
    if (activeHighlight.value === `word-${wordId}`) {
        return true;
    }

    return matchesSearch(label);
};

const tileSearchClass = (highlighted: boolean): string | undefined => {
    if (highlighted) {
        return 'talkie-tile-highlight';
    }

    if (searchIsActive.value || activeHighlight.value) {
        return 'talkie-tile-dimmed';
    }

    return undefined;
};

const currentMenuId = computed(() => props.menu?.id ?? null);

const remoteSearchResults = computed(() => {
    if (!searchIsActive.value) {
        return [];
    }

    const query = normalizedSearch.value;
    const results: { key: string; label: string; href: string; context: string }[] = [];

    for (const item of props.search_index.menus) {
        if (!item.name.toLowerCase().includes(query)) {
            continue;
        }

        if (item.parent_id === currentMenuId.value) {
            continue;
        }

        const parentSlug = item.parent_id
            ? (props.search_index.menus.find((menu) => menu.id === item.parent_id)?.slug ?? null)
            : null;
        const href = item.parent_id
            ? boardUrl(parentSlug, `menu-${item.id}`)
            : boardUrl(null, `menu-${item.id}`);

        results.push({
            key: `menu-${item.id}`,
            label: item.name,
            href,
            context: 'Folder',
        });
    }

    for (const item of props.search_index.words) {
        if (!item.label.toLowerCase().includes(query)) {
            continue;
        }

        if (item.menu_id === currentMenuId.value) {
            continue;
        }

        const href = item.menu_id
            ? boardUrl(item.menu_slug, `word-${item.id}`)
            : boardUrl(null, `word-${item.id}`);

        results.push({
            key: `word-${item.id}`,
            label: item.label,
            href,
            context: item.menu_name,
        });
    }

    return results.slice(0, 8);
});

const clearSearch = () => {
    searchQuery.value = '';
    activeHighlight.value = '';
};

watch(
    () => props.highlight,
    (value) => {
        activeHighlight.value = value?.trim() || '';
    },
);

watch(searchQuery, () => {
    if (searchQuery.value.trim() !== '') {
        activeHighlight.value = '';
    }
});

const scrollHighlightIntoView = async () => {
    await nextTick();
    const el = document.querySelector('.talkie-tile-highlight');
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

onMounted(() => {
    if (activeHighlight.value) {
        void scrollHighlightIntoView();
    }
});

watch(activeHighlight, (value) => {
    if (value) {
        void scrollHighlightIntoView();
    }
});

const wordAccentStyle = (label: string) => {
    const color = boardWordColor(label);

    return color ? { color } : undefined;
};

const addWord = (word: BoardWord) => {
    phrase.value.push({ word });
};

const applyMorph = (kind: MorphKind) => {
    phrase.value = applyMorphToLast(phrase.value, kind);
};

const removeLast = () => {
    phrase.value = removeLastPhrasePart(phrase.value);
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
    speak(textToSpeak(word));
};

let pendingAddTimer: ReturnType<typeof setTimeout> | null = null;
let longPressTimer: ReturnType<typeof setTimeout> | null = null;
let longPressFired = false;
let lastTap: { id: number; at: number } | null = null;

const clearPendingAdd = () => {
    if (pendingAddTimer !== null) {
        clearTimeout(pendingAddTimer);
        pendingAddTimer = null;
    }
};

const clearLongPress = () => {
    if (longPressTimer !== null) {
        clearTimeout(longPressTimer);
        longPressTimer = null;
    }
};

const onWordPointerDown = (word: BoardWord) => {
    if (editMode.value) {
        return;
    }

    longPressFired = false;
    clearLongPress();

    longPressTimer = setTimeout(() => {
        longPressFired = true;
        clearPendingAdd();
        lastTap = null;
        speakWord(word);
    }, LONG_PRESS_MS);
};

const onWordPointerUp = (word: BoardWord, event: PointerEvent) => {
    if (editMode.value) {
        return;
    }

    clearLongPress();

    if (longPressFired) {
        event.preventDefault();
        return;
    }

    const now = Date.now();

    if (lastTap && lastTap.id === word.id && now - lastTap.at < DOUBLE_TAP_MS) {
        clearPendingAdd();
        lastTap = null;
        speakWord(word);
        return;
    }

    lastTap = { id: word.id, at: now };
    clearPendingAdd();
    pendingAddTimer = setTimeout(() => {
        addWord(word);
        pendingAddTimer = null;
    }, DOUBLE_TAP_MS);
};

const onWordPointerCancel = () => {
    clearLongPress();
};

const onWordContextMenu = (event: Event) => {
    event.preventDefault();
};

const openAddWord = () => {
    editingWord.value = null;
    wordDialogOpen.value = true;
};

const openEditWord = (word: BoardWord) => {
    editingWord.value = word;
    wordDialogOpen.value = true;
};

const openAddMenu = () => {
    editingMenu.value = null;
    menuDialogOpen.value = true;
};

const openEditMenu = (menu: BoardMenu) => {
    editingMenu.value = menu;
    menuDialogOpen.value = true;
};

const boardMutation = {
    preserveScroll: true,
    preserveState: true,
    only: ['words', 'menus', 'search_index'] as string[],
    reset: ['search_index'] as string[],
};

const deleteWord = (word: BoardWord) => {
    if (word.is_builtin) {
        return;
    }

    if (!confirm(`Delete “${word.label}”?`)) {
        return;
    }

    router.delete(route('words.destroy', word.id), boardMutation);
};

const hideWord = (word: BoardWord) => {
    router.post(route('words.hide', word.id), {}, boardMutation);
};

const unhideWord = (word: BoardWord) => {
    router.post(route('words.unhide', word.id), {}, boardMutation);
};

const deleteMenu = (menu: BoardMenu) => {
    if (menu.is_builtin) {
        return;
    }

    if (!confirm(`Delete folder “${menu.name}” and everything inside it?`)) {
        return;
    }

    router.delete(route('menus.destroy', menu.id), boardMutation);
};

const hideMenu = (menu: BoardMenu) => {
    router.post(route('menus.hide', menu.id), {}, boardMutation);
};

const unhideMenu = (menu: BoardMenu) => {
    router.post(route('menus.unhide', menu.id), {}, boardMutation);
};

const moveWord = (word: BoardWord, direction: 'up' | 'down') => {
    router.post(route('words.move', word.id), { direction }, boardMutation);
};

const moveMenu = (menu: BoardMenu, direction: 'up' | 'down') => {
    router.post(route('menus.move', menu.id), { direction }, boardMutation);
};

onBeforeUnmount(() => {
    clearPendingAdd();
    clearLongPress();
    document.body.classList.remove('talkie-nested-board');
});
</script>

<template>
    <Head :title="pageTitle" />

    <AppLayout>
        <template #headerActions>
            <div class="relative min-w-0 w-44 sm:w-56">
                <Search class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <Input
                    v-model="searchQuery"
                    type="search"
                    placeholder="Find a word…"
                    class="h-10 rounded-full border-sky-200 bg-white/90 pr-9 pl-9 text-sm font-semibold shadow-sm"
                    aria-label="Search board"
                />
                <button
                    v-if="searchQuery || activeHighlight"
                    type="button"
                    class="absolute top-1/2 right-2 -translate-y-1/2 rounded-full p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                    aria-label="Clear search"
                    @click="clearSearch"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
        </template>

        <div class="flex h-full min-h-0 flex-1 flex-col gap-4 landscape:gap-2">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div v-if="menu">
                    <h1 class="flex items-center gap-2 text-3xl font-extrabold tracking-tight text-slate-800 sm:text-4xl">
                        <BoardMenuIcon :icon="menu.icon" :size="40" icon-class="text-orange-500" />
                        {{ menu.name }}
                    </h1>
                </div>

                <div v-if="ancestors.length" class="flex flex-wrap items-center gap-2 text-sm font-bold text-sky-700">
                    <Link
                        :href="route('board')"
                        :prefetch="['hover', 'click']"
                        cache-for="60s"
                        class="inline-flex items-center gap-1 rounded-full bg-white/80 px-3 py-1 shadow-sm"
                    >
                        <Home class="h-3.5 w-3.5" />
                        Home
                    </Link>
                    <template v-for="ancestor in ancestors" :key="ancestor.id">
                        <span>/</span>
                        <Link
                            :href="boardUrl(ancestor.slug)"
                            :prefetch="['hover', 'click']"
                            cache-for="60s"
                            class="rounded-full bg-white/80 px-3 py-1 shadow-sm"
                        >
                            {{ ancestor.name }}
                        </Link>
                    </template>
                </div>
            </div>

            <div
                v-if="remoteSearchResults.length"
                class="flex flex-wrap items-center gap-2 rounded-2xl border-2 border-amber-200 bg-amber-50/95 px-3 py-2 shadow-sm"
            >
                <span class="text-xs font-bold uppercase tracking-wide text-amber-700">Also found</span>
                <Link
                    v-for="result in remoteSearchResults"
                    :key="result.key"
                    :href="result.href"
                    :prefetch="['hover', 'click']"
                    cache-for="60s"
                    class="inline-flex items-center gap-1 rounded-full border border-amber-300 bg-white px-3 py-1 text-sm font-extrabold text-slate-800 shadow-sm hover:bg-amber-100"
                >
                    {{ result.label }}
                    <span class="text-xs font-bold text-amber-700">{{ result.context }}</span>
                </Link>
            </div>

            <div
                v-if="editMode && can_edit"
                class="sticky top-[4.25rem] z-10 flex flex-wrap items-center justify-between gap-2 rounded-3xl border-2 border-orange-200 bg-orange-50/95 px-4 py-3 shadow-md backdrop-blur"
            >
                <div class="flex flex-wrap gap-2">
                    <Button type="button" class="h-11 rounded-full px-4 font-extrabold" @click="openAddWord">
                        <Plus class="mr-2 h-4 w-4" />
                        Add word
                    </Button>
                    <Button type="button" variant="secondary" class="h-11 rounded-full px-4 font-extrabold" @click="openAddMenu">
                        <FolderPlus class="mr-2 h-4 w-4" />
                        Add folder
                    </Button>
                </div>
                <Button
                    type="button"
                    class="h-11 rounded-full bg-green-600 px-5 font-extrabold text-white shadow-md hover:bg-green-700"
                    @click="exitEditMode"
                >
                    <Check class="mr-2 h-5 w-5" />
                    Done
                </Button>
            </div>

            <div v-else class="sticky top-[4.25rem] z-10 space-y-2 landscape:top-14">
                <div class="rounded-3xl border-2 border-sky-200 bg-white/90 p-4 shadow-md backdrop-blur landscape:p-2">
                    <div class="mb-3 flex min-h-16 flex-wrap items-center gap-2">
                        <span
                            v-for="(token, index) in phrase"
                            :key="`${token.word.id}-${index}-${token.morph ?? 'base'}`"
                            class="talkie-word rounded-full border-2 px-4 py-2 text-base font-extrabold"
                        >
                            {{ displayLabel(token) }}
                        </span>
                        <span v-if="phrase.length === 0" class="inline-flex items-center gap-2 text-base font-semibold text-slate-500">
                            <Sparkles class="h-4 w-4 text-amber-500" />
                            Tap words to build a fun phrase
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2">
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
                        </div>
                        <PhrasesPanel
                            :phrases="phrases"
                            :menu-id="menu?.id ?? null"
                            :menu-name="menu?.name ?? 'Home'"
                            :is-guest="is_guest"
                            :speak="speak"
                        />
                    </div>
                </div>

                <div
                    class="rounded-3xl border-2 border-slate-200 bg-white/90 px-4 py-2 shadow-md backdrop-blur"
                    aria-label="Modifiers"
                >
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Endings</span>
                        <Button
                            v-for="tile in MORPH_TILES"
                            :key="tile.kind"
                            type="button"
                            size="sm"
                            class="h-10 min-w-12 rounded-full border-2 border-slate-300 bg-slate-600 px-4 text-base font-extrabold text-white shadow-sm hover:bg-slate-700"
                            :disabled="phrase.length === 0"
                            @click="applyMorph(tile.kind)"
                        >
                            {{ tile.label }}
                        </Button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 xl:grid-cols-7 landscape:grid-cols-6 landscape:gap-1.5 md:landscape:grid-cols-7">
                <Link
                    v-if="menu"
                    :href="backHref"
                    :prefetch="['hover', 'click']"
                    cache-for="60s"
                    class="talkie-tile border-2 border-orange-600 bg-orange-500 text-white shadow-md transition hover:bg-orange-600 active:scale-95"
                >
                    <ArrowLeft class="h-7 w-7" />
                    Back
                </Link>

                <template v-if="editMode && can_edit">
                    <div
                        v-for="child in menus"
                        :key="`menu-${child.id}`"
                        class="talkie-folder talkie-tile"
                        :class="[child.is_hidden ? 'opacity-45' : undefined, tileSearchClass(isHighlightedMenu(child.id, child.name))]"
                    >
                        <BoardMenuIcon :icon="child.icon" :size="32" />
                        {{ child.name }}
                        <div class="mt-0.5 flex flex-wrap justify-center gap-0.5">
                            <Button
                                type="button"
                                size="icon"
                                class="h-7 w-7 rounded-full border border-slate-200 bg-white text-slate-800 shadow-sm hover:bg-slate-100"
                                @click="moveMenu(child, 'up')"
                            >
                                <ArrowLeft class="h-3.5 w-3.5" />
                            </Button>
                            <Button
                                type="button"
                                size="icon"
                                class="h-7 w-7 rounded-full border border-slate-200 bg-white text-slate-800 shadow-sm hover:bg-slate-100"
                                @click="moveMenu(child, 'down')"
                            >
                                <ArrowRight class="h-3.5 w-3.5" />
                            </Button>
                            <Button
                                type="button"
                                size="icon"
                                class="h-7 w-7 rounded-full border border-slate-200 bg-white text-slate-800 shadow-sm hover:bg-slate-100"
                                @click="openEditMenu(child)"
                            >
                                <Pencil class="h-3.5 w-3.5" />
                            </Button>
                            <Button
                                v-if="child.is_builtin && child.is_hidden"
                                type="button"
                                size="icon"
                                class="h-7 w-7 rounded-full border border-slate-200 bg-white text-slate-800 shadow-sm hover:bg-slate-100"
                                title="Show folder"
                                @click="unhideMenu(child)"
                            >
                                <Eye class="h-3.5 w-3.5" />
                            </Button>
                            <Button
                                v-else-if="child.is_builtin"
                                type="button"
                                size="icon"
                                class="h-7 w-7 rounded-full border border-slate-200 bg-white text-slate-800 shadow-sm hover:bg-slate-100"
                                title="Hide folder"
                                @click="hideMenu(child)"
                            >
                                <EyeOff class="h-3.5 w-3.5" />
                            </Button>
                            <Button
                                v-else
                                type="button"
                                size="icon"
                                variant="destructive"
                                class="h-7 w-7 rounded-full"
                                title="Delete folder"
                                @click="deleteMenu(child)"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </Button>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <Link
                        v-for="child in playableMenus"
                        :key="`menu-${child.id}`"
                        :href="boardUrl(child.slug)"
                        :prefetch="['hover', 'click']"
                        cache-for="60s"
                        class="talkie-folder talkie-tile transition active:scale-95"
                        :class="tileSearchClass(isHighlightedMenu(child.id, child.name))"
                    >
                        <BoardMenuIcon :icon="child.icon" :size="32" />
                        {{ child.name }}
                    </Link>
                </template>

                <template v-if="editMode && can_edit">
                    <div
                        v-for="word in words"
                        :key="`word-${word.id}`"
                        class="talkie-word talkie-tile border-2"
                        :class="[word.is_hidden ? 'opacity-45' : undefined, tileSearchClass(isHighlightedWord(word.id, word.label))]"
                        :style="wordAccentStyle(word.label)"
                    >
                        <BoardWordIcon
                            :label="word.label"
                            :icon="word.icon"
                            :size="26"
                            :icon-class="wordAccentStyle(word.label) ? undefined : 'opacity-70'"
                        />
                        {{ word.label }}
                        <div class="mt-0.5 flex flex-wrap justify-center gap-0.5">
                            <Button
                                type="button"
                                size="icon"
                                class="h-7 w-7 rounded-full border border-slate-300 bg-white text-slate-800 shadow-sm hover:bg-slate-100"
                                @click="moveWord(word, 'up')"
                            >
                                <ArrowLeft class="h-3.5 w-3.5" />
                            </Button>
                            <Button
                                type="button"
                                size="icon"
                                class="h-7 w-7 rounded-full border border-slate-300 bg-white text-slate-800 shadow-sm hover:bg-slate-100"
                                @click="moveWord(word, 'down')"
                            >
                                <ArrowRight class="h-3.5 w-3.5" />
                            </Button>
                            <Button
                                type="button"
                                size="icon"
                                class="h-7 w-7 rounded-full border border-slate-300 bg-white text-slate-800 shadow-sm hover:bg-slate-100"
                                @click="openEditWord(word)"
                            >
                                <Pencil class="h-3.5 w-3.5" />
                            </Button>
                            <Button
                                v-if="word.is_builtin && word.is_hidden"
                                type="button"
                                size="icon"
                                class="h-7 w-7 rounded-full border border-slate-300 bg-white text-slate-800 shadow-sm hover:bg-slate-100"
                                title="Show word"
                                @click="unhideWord(word)"
                            >
                                <Eye class="h-3.5 w-3.5" />
                            </Button>
                            <Button
                                v-else-if="word.is_builtin"
                                type="button"
                                size="icon"
                                class="h-7 w-7 rounded-full border border-slate-300 bg-white text-slate-800 shadow-sm hover:bg-slate-100"
                                title="Hide word"
                                @click="hideWord(word)"
                            >
                                <EyeOff class="h-3.5 w-3.5" />
                            </Button>
                            <Button
                                v-else
                                type="button"
                                size="icon"
                                variant="destructive"
                                class="h-7 w-7 rounded-full"
                                title="Delete word"
                                @click="deleteWord(word)"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </Button>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <button
                        v-for="word in playableWords"
                        :key="`word-${word.id}`"
                        type="button"
                        class="talkie-word talkie-tile touch-manipulation border-2 transition active:scale-95 select-none"
                        :class="tileSearchClass(isHighlightedWord(word.id, word.label))"
                        :style="wordAccentStyle(word.label)"
                        @pointerdown="onWordPointerDown(word)"
                        @pointerup="onWordPointerUp(word, $event)"
                        @pointercancel="onWordPointerCancel"
                        @pointerleave="onWordPointerCancel"
                        @contextmenu="onWordContextMenu"
                    >
                        <BoardWordIcon
                            :label="word.label"
                            :icon="word.icon"
                            :size="26"
                            :icon-class="wordAccentStyle(word.label) ? undefined : 'opacity-70'"
                        />
                        {{ word.label }}
                    </button>
                </template>
            </div>

            <p v-if="menus.length === 0 && words.length === 0" class="text-center text-base font-semibold text-slate-500">
                No words here yet — try another folder.
            </p>

            <p class="flex items-center justify-center gap-2 text-center text-sm font-semibold text-sky-700/80">
                <BookOpen class="h-4 w-4" />
                <span v-if="editMode">Edit mode: add, rename, pronounce, reorder, hide, or delete tiles</span>
                <span v-else>Tip: tap to add · double-tap or press &amp; hold to speak alone</span>
            </p>
        </div>

        <BoardWordDialog
            v-if="can_edit"
            v-model:open="wordDialogOpen"
            :menu-id="menu?.id ?? null"
            :word="editingWord"
            :speak="speak"
        />
        <BoardMenuDialog
            v-if="can_edit"
            v-model:open="menuDialogOpen"
            :parent-id="menu?.id ?? null"
            :menu="editingMenu"
        />
    </AppLayout>
</template>
