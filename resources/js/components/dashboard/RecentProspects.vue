<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from '@lucide/vue';
import { index as clientsIndex } from '@/routes/clients';
import type { DashboardClient } from '@/types/dashboard';

defineProps<{ clients: DashboardClient[]; canView: boolean }>();
</script>

<template>
    <section
        class="min-w-0 rounded-lg border bg-card text-card-foreground"
        aria-labelledby="recent-heading"
    >
        <header class="p-5 sm:p-6">
            <h2 id="recent-heading" class="font-semibold">
                Prospectos recientes
            </h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Los últimos registros en tu CRM.
            </p>
        </header>
        <ul v-if="canView && clients.length" class="divide-y border-t">
            <li v-for="client in clients" :key="client.id">
                <Link
                    :href="client.url"
                    class="flex min-h-16 items-center gap-3 px-5 py-4 transition-colors hover:bg-accent focus-visible:bg-accent focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-ring active:bg-accent motion-reduce:transition-none sm:px-6"
                >
                    <span class="min-w-0 flex-1">
                        <span class="block font-medium break-words">{{
                            client.name
                        }}</span>
                        <span
                            v-if="client.municipality"
                            class="mt-1 block text-xs text-muted-foreground"
                            >{{ client.municipality }}</span
                        >
                    </span>
                    <ChevronRight
                        class="size-4 shrink-0 text-muted-foreground"
                        aria-hidden="true"
                    />
                </Link>
            </li>
        </ul>
        <p
            v-else
            class="px-5 pb-6 text-sm leading-relaxed text-muted-foreground sm:px-6"
        >
            {{
                canView
                    ? 'Aún no hay prospectos. Registra el primero para comenzar a reunir sus recibos y preparar una propuesta.'
                    : 'Tu cuenta no tiene permiso para consultar prospectos.'
            }}
        </p>
        <div v-if="canView" class="border-t px-5 py-2 sm:px-6">
            <Link
                :href="clientsIndex()"
                class="inline-flex min-h-11 items-center text-sm font-medium text-primary underline-offset-4 hover:underline focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-ring"
                >Ver todos los prospectos</Link
            >
        </div>
    </section>
</template>
