<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center p-6 md:p-10">
        <div class="w-full max-w-md">
            <div class="flex flex-col gap-6 rounded-3xl border-2 border-sky-200 bg-white/90 p-6 shadow-xl backdrop-blur sm:p-8">
                <div class="flex flex-col items-center gap-4">
                    <Link :href="route('board')">
                        <AppLogo />
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-2xl font-extrabold tracking-tight text-sky-800">{{ title }}</h1>
                        <p class="text-center text-sm font-semibold text-slate-600">{{ description }}</p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
