<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import TypingKeyboard from '@/components/TypingKeyboard.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Mic, Settings, Smile, Sparkles, UserRound } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <div class="flex min-h-svh flex-col pb-20">
        <header class="sticky top-0 z-20 border-b border-sky-200/70 bg-white/80 px-4 py-3 backdrop-blur-md sm:px-6">
            <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-3">
                <Link :href="route('board')" class="shrink-0">
                    <AppLogo />
                </Link>

                <div class="flex items-center gap-2">
                    <template v-if="user">
                        <Button
                            variant="secondary"
                            size="sm"
                            class="hidden rounded-full font-bold sm:inline-flex"
                            as-child
                        >
                            <Link :href="route('voice.edit')">
                                <Mic class="mr-1.5 h-4 w-4" />
                                Voice
                            </Link>
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            class="hidden rounded-full font-bold sm:inline-flex"
                            as-child
                        >
                            <Link :href="route('profile.edit')">
                                <Settings class="mr-1.5 h-4 w-4" />
                                Settings
                            </Link>
                        </Button>

                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button size="sm" class="rounded-full font-bold">
                                    <Smile class="mr-1.5 h-4 w-4" />
                                    {{ user.preferred_name || user.name }}
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="min-w-56 rounded-2xl">
                                <UserMenuContent :user="user" />
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </template>

                    <Button v-else class="rounded-full font-extrabold shadow-md" as-child>
                        <Link :href="route('personalize')">
                            <Sparkles class="mr-1.5 h-4 w-4" />
                            Personalize
                        </Link>
                    </Button>
                </div>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-6xl flex-1 flex-col px-4 py-4 sm:px-6 sm:py-6">
            <slot />
        </main>

        <footer v-if="!user" class="px-4 pb-4 text-center text-sm font-semibold text-sky-700/80 sm:px-6">
            <span class="inline-flex items-center gap-1">
                <UserRound class="h-4 w-4" />
                Tap words to speak — personalize anytime to save your voice
            </span>
        </footer>

        <TypingKeyboard />
    </div>
</template>
