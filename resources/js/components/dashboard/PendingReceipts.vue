<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Check, FileText } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import type { DashboardPending } from '@/types/dashboard';

defineProps<{ items: DashboardPending[]; canView: boolean }>();

const date = (value: string) =>
    new Intl.DateTimeFormat('es-MX', { day: 'numeric', month: 'short' }).format(
        new Date(value),
    );
</script>

<template>
    <section
        class="min-w-0 overflow-hidden rounded-lg border bg-card text-card-foreground"
        aria-labelledby="pending-heading"
    >
        <header class="border-b p-5 sm:p-6">
            <h2
                id="pending-heading"
                class="text-lg font-semibold tracking-tight"
            >
                Requieren tu atención
            </h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Continúa la revisión de recibos y la selección de equipos.
            </p>
        </header>
        <ul v-if="canView && items.length" class="divide-y">
            <li
                v-for="item in items"
                :key="item.id"
                class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:p-6"
            >
                <div class="flex min-w-0 flex-1 gap-3">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-md bg-muted text-muted-foreground"
                    >
                        <FileText class="size-5" aria-hidden="true" />
                    </div>
                    <div class="min-w-0">
                        <p class="font-medium break-words">
                            {{ item.client_name }}
                        </p>
                        <div
                            class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground"
                        >
                            <span>Recibo #{{ item.id }}</span
                            ><time :datetime="item.created_at">{{
                                date(item.created_at)
                            }}</time>
                        </div>
                        <p
                            class="mt-2 inline-flex rounded-md bg-secondary px-2 py-1 text-xs font-medium text-secondary-foreground"
                        >
                            {{ item.status_label }}
                        </p>
                    </div>
                </div>
                <Button
                    as-child
                    variant="outline"
                    class="min-h-11 self-start active:bg-accent motion-reduce:transition-none sm:self-center"
                >
                    <Link
                        :href="item.url"
                        :aria-label="`${item.action_label}: ${item.client_name}, recibo ${item.id}`"
                        >{{ item.action_label }}</Link
                    >
                </Button>
            </li>
        </ul>
        <div v-else class="px-6 py-12 sm:py-16">
            <div
                v-if="canView"
                class="mb-4 flex size-10 items-center justify-center rounded-full bg-primary/10 text-primary"
            >
                <Check class="size-5" aria-hidden="true" />
            </div>
            <h3 class="font-medium">
                {{
                    canView
                        ? 'Sin recibos pendientes de atención'
                        : 'Acceso a recibos restringido'
                }}
            </h3>
            <p
                class="mt-2 max-w-md text-sm leading-relaxed text-muted-foreground"
            >
                {{
                    canView
                        ? 'Aquí aparecerán los recibos que necesiten revisión o una acción para continuar.'
                        : 'Tu cuenta no tiene permiso para consultar los recibos. Solicita acceso a un administrador si lo necesitas.'
                }}
            </p>
        </div>
    </section>
</template>
