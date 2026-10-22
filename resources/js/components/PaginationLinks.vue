<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: Array<{ url: string | null; label: string; active: boolean }>;
}>();

function label(text: string): string {
    if (text.includes('Previous') || text.includes('Anterior'))
        return 'Anterior';
    if (text.includes('Next') || text.includes('Siguiente')) return 'Siguiente';
    return text
        .replace(/&[^;]+;/g, '')
        .replace(/<[^>]*>/g, '')
        .trim();
}
</script>

<template>
    <nav
        v-if="links.length > 3"
        aria-label="Paginación"
        class="flex flex-wrap gap-2"
    >
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                :aria-current="link.active ? 'page' : undefined"
                :class="[
                    'rounded-md border px-3 py-2 text-sm',
                    link.active
                        ? 'bg-primary text-primary-foreground'
                        : 'hover:bg-accent',
                ]"
                preserve-scroll
            >
                {{ label(link.label) }}
            </Link>
            <span
                v-else
                class="rounded-md border px-3 py-2 text-sm text-muted-foreground"
                aria-disabled="true"
                >{{ label(link.label) }}</span
            >
        </template>
    </nav>
</template>
