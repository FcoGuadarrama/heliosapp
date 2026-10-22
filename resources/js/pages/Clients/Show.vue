<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { show as clientsShow } from '@/routes/clients';

interface ClientData {
    id: number;
    type: string;
    name: string;
    contact_name: string | null;
    email: string | null;
    phone: string | null;
    rfc: string | null;
    street: string | null;
    exterior_number: string | null;
    interior_number: string | null;
    neighborhood: string | null;
    municipality: string | null;
    state: string | null;
    postal_code: string | null;
    notes: string | null;
}

interface Project {
    id: number;
    code: string;
    name: string;
    status: string;
    created_at: string;
}

const page = usePage();
const client = computed(() => page.props.client as unknown as ClientData);
const projects = computed(() => page.props.projects as unknown as Project[]);

const typeLabels: Record<string, string> = {
    residential: 'Residencial',
    commercial: 'Comercial',
    industrial: 'Industrial',
    agricultural: 'Agrícola',
};

const statusLabels: Record<string, string> = {
    draft: 'Borrador',
    data_received: 'Datos recibidos',
    analyzing: 'Analizando',
    analyzed: 'Analizado',
    quoted: 'Presupuestado',
    proposal_sent: 'Propuesta enviada',
    won: 'Ganado',
    lost: 'Perdido',
    archived: 'Archivado',
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Clientes',
                href: '/clients',
            },
            {
                title: 'Detalle',
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Cliente - ${client.name}`" />

    <div class="flex flex-col space-y-6">
        <div class="flex items-center justify-between">
            <Heading
                variant="small"
                :title="client.name"
                :description="typeLabels[client.type]"
            />

            <Link
                :href="`/clients/${client.id}/edit`"
                as="button"
                class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-8 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
            >
                Editar cliente
            </Link>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border p-6">
                <h3 class="mb-4 text-lg font-semibold">Información general</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-muted-foreground">Nombre de contacto</p>
                        <p class="font-medium">
                            {{ client.contact_name || '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Correo</p>
                        <p class="font-medium">
                            {{ client.email || '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Teléfono</p>
                        <p class="font-medium">
                            {{ client.phone || '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">RFC</p>
                        <p class="font-medium">
                            {{ client.rfc || '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border p-6">
                <h3 class="mb-4 text-lg font-semibold">Dirección</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-muted-foreground">Calle</p>
                        <p class="font-medium">
                            {{ client.street || '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Número exterior</p>
                        <p class="font-medium">
                            {{ client.exterior_number || '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Número interior</p>
                        <p class="font-medium">
                            {{ client.interior_number || '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Colonia</p>
                        <p class="font-medium">
                            {{ client.neighborhood || '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Municipio</p>
                        <p class="font-medium">
                            {{ client.municipality || '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Estado</p>
                        <p class="font-medium">
                            {{ client.state || '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Código postal</p>
                        <p class="font-medium">
                            {{ client.postal_code || '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <div v-if="client.notes" class="rounded-lg border p-6">
                <h3 class="mb-4 text-lg font-semibold">Notas</h3>
                <p class="text-sm">{{ client.notes }}</p>
            </div>

            <div class="rounded-lg border p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Proyectos</h3>
                    <Link
                        href="/projects/create"
                        as="button"
                        class="inline-flex h-9 items-center justify-center rounded-md border border-input bg-background px-3 text-sm font-medium shadow-sm hover:bg-accent"
                    >
                        Nuevo proyecto
                    </Link>
                </div>
                <div class="mt-4">
                    <div v-if="projects.length === 0" class="text-center text-muted-foreground">
                        <p>No hay proyectos para este cliente</p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium">Código</th>
                                    <th class="px-4 py-2 text-left font-medium">Nombre</th>
                                    <th class="px-4 py-2 text-left font-medium">Estado</th>
                                    <th class="px-4 py-2 text-left font-medium">Fecha</th>
                                    <th class="px-4 py-2 text-left font-medium">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="project in projects"
                                    :key="project.id"
                                    class="border-t"
                                >
                                    <td class="px-4 py-2">{{ project.code }}</td>
                                    <td class="px-4 py-2">{{ project.name }}</td>
                                    <td class="px-4 py-2">
                                        <Badge variant="secondary">
                                            {{ statusLabels[project.status] }}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-2">{{ project.created_at }}</td>
                                    <td class="px-4 py-2">
                                        <Link
                                            :href="`/projects/${project.id}`"
                                            as="button"
                                            class="text-sm text-primary hover:underline"
                                        >
                                            Ver
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
