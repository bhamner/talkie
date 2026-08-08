<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useSpeech } from '@/composables/useSpeech';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Volume2 } from 'lucide-vue-next';

const props = defineProps<{
    preferred_name: string | null;
}>();

const { speak } = useSpeech();

const form = useForm({
    preferred_name: props.preferred_name ?? '',
});

const hear = () => {
    if (form.preferred_name.trim()) {
        speak(form.preferred_name.trim());
    }
};

const submit = () => {
    form.put(route('onboarding.name.update'));
};
</script>

<template>
    <AuthBase title="Hello!" description="What can we call you?">
        <Head title="Your name" />

        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="preferred_name" class="text-base font-extrabold text-sky-800">First name</Label>
                <Input
                    id="preferred_name"
                    v-model="form.preferred_name"
                    type="text"
                    required
                    autofocus
                    maxlength="80"
                    autocomplete="given-name"
                    class="h-12 rounded-2xl border-2 border-sky-200 text-lg font-bold"
                    placeholder="Your first name"
                />
                <InputError :message="form.errors.preferred_name" />
            </div>

            <div class="flex flex-wrap gap-2">
                <Button type="button" variant="secondary" class="rounded-full" :disabled="!form.preferred_name.trim()" @click="hear">
                    <Volume2 class="mr-2 h-4 w-4" />
                    Hear it
                </Button>
                <Button type="submit" class="rounded-full" :disabled="form.processing || !form.preferred_name.trim()">
                    Save & continue
                </Button>
            </div>
        </form>
    </AuthBase>
</template>
