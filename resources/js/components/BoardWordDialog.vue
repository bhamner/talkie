<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { Volume2 } from 'lucide-vue-next';
import { watch } from 'vue';

type BoardWord = {
    id: number;
    label: string;
    speak_text: string | null;
};

const props = defineProps<{
    open: boolean;
    menuId: number | null;
    word: BoardWord | null;
    speak: (text: string) => void;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    label: '',
    speak_text: '',
    menu_id: null as number | null,
});

watch(
    () => [props.open, props.word, props.menuId] as const,
    ([open, word, menuId]) => {
        if (!open) {
            return;
        }

        form.clearErrors();
        form.label = word?.label ?? '';
        form.speak_text = word?.speak_text ?? '';
        form.menu_id = menuId;
    },
);

const close = () => {
    emit('update:open', false);
    form.clearErrors();
};

const preview = () => {
    const text = (form.speak_text.trim() || form.label.trim()).trim();
    if (text) {
        props.speak(text);
    }
};

const submit = () => {
    form.speak_text = form.speak_text.trim();
    form.label = form.label.trim();

    const options = {
        preserveScroll: true,
        onSuccess: () => close(),
    };

    if (props.word) {
        form.put(route('words.update', props.word.id), options);
        return;
    }

    form.post(route('words.store'), options);
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <form class="space-y-4" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>{{ word ? 'Edit word' : 'Add word' }}</DialogTitle>
                    <DialogDescription>
                        Choose the tile label and how Talkie should speak it.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label for="word-label" class="font-extrabold text-sky-800">Label</Label>
                    <Input
                        id="word-label"
                        v-model="form.label"
                        type="text"
                        maxlength="100"
                        required
                        class="h-12 rounded-2xl border-2 border-sky-200 text-lg font-bold"
                        placeholder="Word on the tile"
                    />
                    <InputError :message="form.errors.label" />
                </div>

                <div class="grid gap-2">
                    <Label for="word-speak" class="font-extrabold text-sky-800">Speak as (optional)</Label>
                    <Input
                        id="word-speak"
                        v-model="form.speak_text"
                        type="text"
                        maxlength="255"
                        class="h-12 rounded-2xl border-2 border-sky-200 text-lg font-bold"
                        placeholder="How Talkie should say this word"
                    />
                    <InputError :message="form.errors.speak_text" />
                </div>

                <DialogFooter class="gap-2 sm:justify-between">
                    <Button type="button" variant="secondary" class="rounded-full font-bold" @click="preview">
                        <Volume2 class="mr-2 h-4 w-4" />
                        Preview
                    </Button>
                    <div class="flex gap-2">
                        <Button type="button" variant="outline" class="rounded-full font-bold" @click="close">
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            class="rounded-full font-extrabold"
                            :disabled="form.processing || !form.label.trim()"
                        >
                            {{ word ? 'Save' : 'Add word' }}
                        </Button>
                    </div>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
