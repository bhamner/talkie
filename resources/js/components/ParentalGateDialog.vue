<script setup lang="ts">
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
import { createMathChallenge, type MathChallenge } from '@/lib/parentalGate';
import { ref, watch } from 'vue';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    unlocked: [];
}>();

const challenge = ref<MathChallenge>(createMathChallenge());
const answer = ref('');
const error = ref('');

const resetChallenge = (keepError = false) => {
    challenge.value = createMathChallenge();
    answer.value = '';

    if (!keepError) {
        error.value = '';
    }
};

watch(
    () => props.open,
    (open) => {
        if (open) {
            resetChallenge();
        }
    },
);

const close = () => {
    emit('update:open', false);
};

const submit = () => {
    const parsed = Number.parseInt(answer.value.trim(), 10);

    if (Number.isNaN(parsed) || parsed !== challenge.value.answer) {
        error.value = 'Not quite — try again.';
        resetChallenge(true);
        return;
    }

    emit('unlocked');
    emit('update:open', false);
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <form class="space-y-4" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>Grown-ups only</DialogTitle>
                    <DialogDescription>
                        Solve this quick math problem to open Settings.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label for="parental-gate-answer" class="font-extrabold text-sky-800">
                        What is {{ challenge.prompt }}?
                    </Label>
                    <Input
                        id="parental-gate-answer"
                        v-model="answer"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        autocomplete="off"
                        autofocus
                        class="h-14 rounded-2xl border-2 border-sky-200 text-center text-2xl font-extrabold tracking-wide"
                        placeholder="?"
                    />
                    <p v-if="error" class="text-sm font-semibold text-red-600">{{ error }}</p>
                </div>

                <DialogFooter class="gap-2">
                    <Button type="button" variant="outline" class="rounded-full font-bold" @click="close">
                        Cancel
                    </Button>
                    <Button type="submit" class="rounded-full font-extrabold" :disabled="!answer.trim()">
                        Continue
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
