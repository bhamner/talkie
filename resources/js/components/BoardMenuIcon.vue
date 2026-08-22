<script setup lang="ts">
import { boardFolderIconByKey } from '@/lib/boardFolderIcons';
import { HugeiconsIcon } from '@hugeicons/vue';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        icon?: string | null;
        size?: number;
        iconClass?: string;
    }>(),
    {
        size: 32,
        icon: null,
    },
);

const folderIcon = computed(() => boardFolderIconByKey(props.icon));
</script>

<template>
    <HugeiconsIcon
        v-if="folderIcon"
        :icon="folderIcon"
        :size="size"
        color="currentColor"
        :stroke-width="2"
        :class="iconClass"
    />
</template>
