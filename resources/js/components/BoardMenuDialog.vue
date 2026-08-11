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
import { watch } from 'vue';

type BoardMenu = {
    id: number;
    name: string;
};

const props = defineProps<{
    open: boolean;
    parentId: number | null;
    menu: BoardMenu | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    name: '',
    parent_id: null as number | null,
});

watch(
    () => [props.open, props.menu, props.parentId] as const,
    ([open, menu, parentId]) => {
        if (!open) {
            return;
        }

        form.clearErrors();
        form.name = menu?.name ?? '';
        form.parent_id = parentId;
    },
);

const close = () => {
    emit('update:open', false);
    form.clearErrors();
};

const submit = () => {
    form.name = form.name.trim();

    const options = {
        preserveScroll: true,
        onSuccess: () => close(),
    };

    if (props.menu) {
        form.put(route('menus.update', props.menu.id), options);
        return;
    }

    form.post(route('menus.store'), options);
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <form class="space-y-4" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>{{ menu ? 'Rename folder' : 'Add folder' }}</DialogTitle>
                    <DialogDescription>
                        Folders group related words on the board.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label for="menu-name" class="font-extrabold text-sky-800">Folder name</Label>
                    <Input
                        id="menu-name"
                        v-model="form.name"
                        type="text"
                        maxlength="100"
                        required
                        class="h-12 rounded-2xl border-2 border-sky-200 text-lg font-bold"
                        placeholder="e.g. School"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <DialogFooter class="gap-2">
                    <Button type="button" variant="outline" class="rounded-full font-bold" @click="close">
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        class="rounded-full font-extrabold"
                        :disabled="form.processing || !form.name.trim()"
                    >
                        {{ menu ? 'Save' : 'Add folder' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
