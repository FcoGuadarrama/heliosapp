<script setup lang="ts">
import { computed } from 'vue';
import type { DashboardOverview } from '@/types/dashboard';

const props = defineProps<{ metrics: DashboardOverview['metrics'] }>();

const visibleMetrics = computed(() =>
    [
        {
            label: 'Prospectos',
            value: props.metrics.clients,
            detail: 'En tu CRM',
        },
        {
            label: 'Requieren atención',
            value: props.metrics.review,
            detail: 'Recibos pendientes',
        },
        {
            label: 'En proceso',
            value: props.metrics.processing,
            detail: 'Análisis de recibos',
        },
        {
            label: 'Borradores de equipos',
            value: props.metrics.quotes,
            detail: 'Expedientes con selección',
        },
    ].filter((metric) => metric.value !== null),
);
</script>

<template>
    <dl
        v-if="visibleMetrics.length"
        class="grid grid-cols-2 divide-sidebar-border overflow-hidden rounded-xl border border-sidebar-border bg-sidebar text-sidebar-foreground sm:flex sm:divide-x"
        aria-label="Resumen de operación"
    >
        <div
            v-for="metric in visibleMetrics"
            :key="metric.label"
            class="min-w-0 flex-1 p-4 sm:p-5"
        >
            <dt class="text-sm font-medium">{{ metric.label }}</dt>
            <dd
                class="mt-3 text-4xl font-medium tracking-tight text-sidebar-primary tabular-nums"
            >
                {{ metric.value?.toLocaleString('es-MX') }}
            </dd>
            <dd class="mt-1 text-xs text-sidebar-foreground/80">
                {{ metric.detail }}
            </dd>
        </div>
    </dl>
</template>
