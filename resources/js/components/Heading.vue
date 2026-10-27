<script setup lang="ts">
type Props = {
    title: string;
    description?: string;
    variant?: 'default' | 'small';
};

withDefaults(defineProps<Props>(), {
    variant: 'default',
});
</script>

<template>
    <header :class="variant === 'small' ? '' : 'space-y-2'">
        <component
            :is="variant === 'small' ? 'h2' : 'h1'"
            :class="
                variant === 'small'
                    ? 'mb-0.5 text-base font-medium'
                    : 'text-2xl font-semibold tracking-tight sm:text-3xl'
            "
        >
            {{ title }}
        </component>
        <p
            v-if="description"
            class="max-w-2xl text-sm leading-relaxed text-muted-foreground"
        >
            {{ description }}
        </p>
    </header>
</template>
