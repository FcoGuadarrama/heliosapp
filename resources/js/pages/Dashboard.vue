<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, RefreshCw, Upload } from '@lucide/vue';
import { computed, ref } from 'vue';
import DashboardMetrics from '@/components/dashboard/DashboardMetrics.vue';
import PendingReceipts from '@/components/dashboard/PendingReceipts.vue';
import RecentProspects from '@/components/dashboard/RecentProspects.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { create as clientsCreate } from '@/routes/clients';
import { create as intakesCreate } from '@/routes/intakes';
import type { DashboardOverview } from '@/types/dashboard';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Resumen', href: dashboard() }] },
});

const props = defineProps<{ overview: DashboardOverview }>();
const refreshing = ref(false);
const refreshError = ref(false);
const updatedTime = computed(() =>
    new Intl.DateTimeFormat('es-MX', {
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(props.overview.updated_at)),
);

function reportRefreshError() {
    refreshError.value = true;
    return false;
}

function refresh() {
    refreshing.value = true;
    refreshError.value = false;
    router.reload({
        only: ['overview'],
        onError: reportRefreshError,
        onHttpException: reportRefreshError,
        onNetworkError: reportRefreshError,
        onFinish: () => {
            refreshing.value = false;
        },
    });
}
</script>

<template>
    <Head title="Resumen" />
    <div class="space-y-6 sm:space-y-8">
        <header
            class="flex flex-col justify-between gap-5 lg:flex-row lg:items-center"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">
                    Resumen
                </h1>
                <p
                    class="mt-2 max-w-xl text-sm leading-relaxed text-muted-foreground"
                >
                    Tus prospectos, recibos y próximos pasos para preparar
                    cotizaciones solares.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button
                    v-if="overview.capabilities.create_clients"
                    as-child
                    variant="outline"
                    class="min-h-11 active:bg-accent motion-reduce:transition-none"
                >
                    <Link :href="clientsCreate()"
                        ><Plus aria-hidden="true" />Nuevo prospecto</Link
                    >
                </Button>
                <Button
                    v-if="overview.capabilities.import_receipts"
                    as-child
                    class="min-h-11 active:opacity-80 motion-reduce:transition-none"
                >
                    <Link :href="intakesCreate()"
                        ><Upload aria-hidden="true" />Recibir recibo</Link
                    >
                </Button>
            </div>
        </header>
        <DashboardMetrics :metrics="overview.metrics" />
        <div
            class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.8fr)_minmax(0,1fr)]"
        >
            <PendingReceipts
                :items="overview.pending"
                :can-view="overview.capabilities.view_receipts"
            />
            <RecentProspects
                :clients="overview.recent_clients"
                :can-view="overview.capabilities.view_clients"
            />
        </div>
        <footer
            class="flex flex-wrap items-center justify-between gap-3 border-t pt-3"
        >
            <p
                role="status"
                aria-live="polite"
                class="text-xs text-muted-foreground"
            >
                <template v-if="refreshError"
                    >No se pudo actualizar. Vuelve a intentarlo.</template
                >
                <template v-else-if="refreshing"
                    >Actualizando resumen…</template
                >
                <template v-else
                    >Actualizado a las
                    <time :datetime="overview.updated_at">{{
                        updatedTime
                    }}</time></template
                >
            </p>
            <Button
                variant="ghost"
                class="min-h-11 active:bg-accent motion-reduce:transition-none"
                :disabled="refreshing"
                @click="refresh"
            >
                <RefreshCw
                    :class="{
                        'animate-spin motion-reduce:animate-none': refreshing,
                    }"
                    aria-hidden="true"
                />
                {{ refreshing ? 'Actualizando…' : 'Actualizar' }}
            </Button>
        </footer>
    </div>
</template>
