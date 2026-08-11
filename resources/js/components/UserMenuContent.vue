<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import { useBoardEditMode } from '@/composables/useBoardEditMode';
import type { User } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Mic, Pencil, UserRound } from 'lucide-vue-next';

interface Props {
    user: User;
}

defineProps<Props>();

const { enterEditMode } = useBoardEditMode();

const goEditBoard = () => {
    const onBoard = route().current('board');

    if (onBoard) {
        enterEditMode();
        return;
    }

    router.visit(route('board'), {
        onSuccess: () => enterEditMode(),
    });
};
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem @select="goEditBoard">
            <Pencil class="mr-2 h-4 w-4" />
            Edit board
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('voice.edit')" as="button">
                <Mic class="mr-2 h-4 w-4" />
                Voice
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('profile.edit')" as="button">
                <UserRound class="mr-2 h-4 w-4" />
                Profile
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link class="block w-full" method="post" :href="route('logout')" as="button">
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>
