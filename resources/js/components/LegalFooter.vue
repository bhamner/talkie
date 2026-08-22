<script setup lang="ts">
const links = [
    { title: 'Privacy', name: 'privacy' },
    { title: 'Terms', name: 'terms' },
    { title: 'Cookies', name: 'cookies' },
] as const;
</script>

<template>
    <nav class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-sm font-semibold" aria-label="Legal">
        <a
            v-for="link in links"
            :key="link.name"
            :href="route(link.name)"
            class="text-sky-700/80 underline-offset-4 hover:text-sky-900 hover:underline"
        >
            {{ link.title }}
        </a>
    </nav>
</template>
