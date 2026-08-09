<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Link, useForm } from '@inertiajs/vue3';
import { MessageSquareText, Plus, Volume2, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

type BoardPhrase = {
    id: number;
    text: string;
};

const props = defineProps<{
    phrases: BoardPhrase[];
    menuId: number | null;
    menuName: string;
    isGuest: boolean;
    speak: (text: string) => void;
}>();

const open = ref(false);

const form = useForm({
    text: '',
    menu_id: props.menuId as number | null,
});

watch(
    () => props.menuId,
    (menuId) => {
        form.menu_id = menuId;
    },
);

const speakPhrase = (text: string) => {
    props.speak(text);
};

const submit = () => {
    form.post(route('phrases.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('text');
        },
    });
};
</script>

<template>
    <div>
        <Button
            type="button"
            variant="secondary"
            class="h-12 rounded-full px-5 text-base font-extrabold"
            :aria-expanded="open"
            aria-controls="talkie-phrases-panel"
            @click="open = !open"
        >
            <MessageSquareText class="mr-2 h-5 w-5" />
            Phrases
        </Button>

        <Teleport to="body">
            <div
                v-if="open"
                class="fixed inset-0 z-[200] flex items-stretch justify-center bg-sky-950/40 p-3 sm:p-5"
                @click.self="open = false"
            >
                <div
                    id="talkie-phrases-panel"
                    class="flex h-full w-full max-w-5xl flex-col overflow-hidden rounded-3xl border-2 border-violet-200 bg-white shadow-2xl"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Phrases"
                >
                    <div class="flex shrink-0 items-start justify-between gap-3 border-b border-violet-100 px-4 py-4 sm:px-6">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wide text-violet-600">Phrases</p>
                            <h2 class="text-2xl font-extrabold text-slate-800 sm:text-3xl">{{ menuName }}</h2>
                        </div>
                        <Button type="button" variant="ghost" size="icon" class="shrink-0 rounded-full" @click="open = false">
                            <X class="h-5 w-5" />
                            <span class="sr-only">Close phrases</span>
                        </Button>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4 sm:px-6">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <button
                                v-for="item in phrases"
                                :key="item.id"
                                type="button"
                                class="flex min-h-16 items-center gap-3 rounded-2xl border-2 border-violet-100 bg-violet-50 px-4 py-4 text-left text-lg font-extrabold text-violet-950 shadow-sm transition hover:bg-violet-100 active:scale-[0.99] sm:text-xl"
                                @click="speakPhrase(item.text)"
                            >
                                <Volume2 class="h-5 w-5 shrink-0 opacity-70" />
                                <span>{{ item.text }}</span>
                            </button>

                            <p
                                v-if="phrases.length === 0"
                                class="col-span-full rounded-2xl bg-slate-50 px-4 py-10 text-center text-base font-semibold text-slate-500"
                            >
                                No phrases here yet — add one below.
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0 border-t border-sky-100 bg-sky-50/80 px-4 py-4 sm:px-6">
                        <template v-if="isGuest">
                            <p class="mb-3 text-sm font-semibold text-slate-600">
                                Personalize to save your own phrases in this menu.
                            </p>
                            <Button class="rounded-full font-extrabold" as-child>
                                <Link :href="route('personalize')">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Personalize to add phrases
                                </Link>
                            </Button>
                        </template>

                        <form v-else class="flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="submit">
                            <div class="grid min-w-0 flex-1 gap-2">
                                <Label for="phrase-text" class="text-base font-extrabold text-sky-800">
                                    Add a phrase
                                </Label>
                                <Input
                                    id="phrase-text"
                                    v-model="form.text"
                                    type="text"
                                    maxlength="255"
                                    required
                                    class="h-12 rounded-2xl border-2 border-sky-200 text-lg font-bold"
                                    :placeholder="`Example for ${menuName}`"
                                />
                                <InputError :message="form.errors.text" />
                                <InputError :message="form.errors.menu_id" />
                            </div>
                            <Button
                                type="submit"
                                class="h-12 shrink-0 rounded-full px-5 text-base font-extrabold"
                                :disabled="form.processing || !form.text.trim()"
                            >
                                <Plus class="mr-2 h-5 w-5" />
                                Save phrase
                            </Button>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
