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
import { Settings, Sparkles, UserRound } from 'lucide-vue-next';
import { computed, nextTick, ref } from 'vue';

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);

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
    <div class="flex min-h-svh flex-col pb-20">
        <header class="sticky top-0 z-20 border-b border-sky-200/70 bg-white/80 px-2 py-3 backdrop-blur-md sm:px-3">
            <div class="flex w-full items-center justify-between gap-3">
                <Link :href="route('board')" class="shrink-0">
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

                    <Button v-else class="rounded-full font-extrabold shadow-md" as-child>
                        <Link :href="route('personalize')">
                            <Sparkles class="mr-1.5 h-4 w-4" />
                            Personalize
                        </Link>
                    </Button>
                </div>
            </div>

            <VoiceProgressBanner class="mt-3" />
        </header>

        <main class="flex w-full flex-1 flex-col px-2 py-3 sm:px-3 sm:py-4">
            <slot />
        </main>

        <footer v-if="!user" class="px-2 pb-4 text-center text-sm font-semibold text-sky-700/80 sm:px-3">
            <span class="inline-flex items-center gap-1">
                <UserRound class="h-4 w-4" />
                Tap words to speak — personalize anytime to save your voice
            </span>
        </footer>

        <TypingKeyboard />
    </div>
</template>
