<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({ inheritAttrs: false });
defineProps<{ className?: HTMLAttributes['class'] }>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="1330 285 420 300"
        :class="className"
        aria-hidden="true"
        v-bind="$attrs"
    >
        <image href="/images/helios-brand.jpg" width="2048" height="1142" />
    </svg>
</template>
