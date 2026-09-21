<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import ParentalGateDialog from '@/components/ParentalGateDialog.vue';
import TypingKeyboard from '@/components/TypingKeyboard.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import VoiceProgressBanner from '@/components/VoiceProgressBanner.vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { useMediaQuery } from '@vueuse/core';
import { Settings, Sparkles, UserRound } from 'lucide-vue-next';
import { computed, nextTick, ref } from 'vue';

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);
const isSmUp = useMediaQuery('(min-width: 640px)');

const gateOpen = ref(false);
const menuOpen = ref(false);

const requestSettings = () => {
    if (menuOpen.value) {
        menuOpen.value = false;
        return;
    }

    gateOpen.value = true;
};

const onMenuOpenChange = (open: boolean) => {
    // Only close from outside interaction; opening always goes through the gate.
    if (!open) {
        menuOpen.value = false;
    }
};

const onGateUnlocked = async () => {
    await nextTick();
    menuOpen.value = true;
};
</script>

<template>
    <div
        class="flex h-dvh max-h-dvh min-h-0 max-w-full flex-col overflow-hidden pt-[env(safe-area-inset-top)]"
    >
        <header
            class="z-20 shrink-0 border-b border-sky-200/70 bg-white/80 px-2 py-3 backdrop-blur-md sm:px-3 landscape:py-2"
        >
            <div class="flex w-full min-w-0 items-center justify-between gap-3">
                <Link :href="route('board')" class="shrink-0" aria-label="Talkie home">
                    <AppLogo />
                </Link>

                <div class="flex items-center gap-2">
                    <slot name="headerActions" />

                    <template v-if="user">
                        <DropdownMenu :open="menuOpen" @update:open="onMenuOpenChange">
                            <DropdownMenuTrigger as-child>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="rounded-full font-bold"
                                    @pointerdown.prevent
                                    @click="requestSettings"
                                >
                                    <Settings class="mr-1.5 h-4 w-4" />
                                    Settings
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="min-w-56 rounded-2xl">
                                <UserMenuContent :user="user" />
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <ParentalGateDialog v-model:open="gateOpen" @unlocked="onGateUnlocked" />
                    </template>

                    <Button
                        v-else
                        class="h-10 w-10 gap-0 rounded-full p-0 font-extrabold shadow-md sm:h-10 sm:w-auto sm:gap-2 sm:px-4"
                        as-child
                    >
                        <Link :href="route('personalize')" aria-label="Personalize">
                            <Sparkles class="h-4 w-4" />
                            <span class="hidden sm:inline">Personalize</span>
                        </Link>
                    </Button>
                </div>
            </div>

            <VoiceProgressBanner class="mt-3 landscape:mt-2" />
        </header>

        <main
            class="flex min-h-0 w-full flex-1 flex-col overflow-hidden px-2 pt-[10px] sm:px-3"
        >
            <slot />
        </main>

        <footer
            v-if="!user && isSmUp"
            class="shrink-0 px-3 py-4 text-center text-sm font-semibold text-sky-700/80"
        >
            <span class="inline-flex items-center gap-1">
                <UserRound class="h-4 w-4" />
                Tap words to speak — personalize anytime to save your voice
            </span>
        </footer>

        <TypingKeyboard />
    </div>
</template>
