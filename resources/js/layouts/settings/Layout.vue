<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, Mic, Palette, Shield, UserRound } from 'lucide-vue-next';

const sidebarNavItems: (NavItem & { icon: typeof Mic })[] = [
    {
        title: 'Voice',
        href: '/settings/voice',
        icon: Mic,
    },
    {
        title: 'Profile',
        href: '/settings/profile',
        icon: UserRound,
    },
    {
        title: 'Password',
        href: '/settings/password',
        icon: Shield,
    },
    {
        title: 'Appearance',
        href: '/settings/appearance',
        icon: Palette,
    },
];

const currentPath = window.location.pathname;
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <Heading title="Settings" description="Tune Talkie for you" />
            <Button variant="outline" class="rounded-full font-bold" as-child>
                <Link :href="route('board')">
                    <ArrowLeft class="mr-1.5 h-4 w-4" />
                    Back to board
                </Link>
            </Button>
        </div>

        <div class="flex flex-col gap-6 lg:flex-row">
            <aside class="w-full lg:w-56">
                <nav class="flex flex-wrap gap-2 lg:flex-col">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="outline"
                        :class="[
                            'rounded-full font-bold lg:justify-start',
                            currentPath === item.href
                                ? 'border-orange-300 bg-orange-100 text-orange-900'
                                : 'bg-white/80',
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" class="mr-1.5 h-4 w-4" />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <div class="flex-1 rounded-3xl border-2 border-sky-200 bg-white/90 p-5 shadow-md sm:p-6">
                <section class="max-w-xl space-y-8">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
