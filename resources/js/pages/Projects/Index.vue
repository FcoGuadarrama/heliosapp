<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const confirmDelete = (message: string): boolean => {
    return window.confirm(message);
};
import Heading from '@/components/Heading.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { Badge } from '@/components/ui/badge';
import { Button, buttonVariants } from '@/components/ui/button';
import { index as projectsIndex } from '@/routes/projects';

interface Project {
    id: number;
    code: string;
    name: string;
    client_name: string;
    status: string;
    created_at: string;
}

interface PaginatedProjects {
    data: Project[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

const page = usePage();
const projects = computed(
    () => (page.props.projects as unknown as PaginatedProjects).data,
);

const pagination = computed(
    () => (page.props.projects as unknown as PaginatedProjects).links,
);

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

const statusColors: Record<
    string,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    draft: 'secondary',
    data_received: 'default',
    analyzing: 'default',
    analyzed: 'default',
    quoted: 'default',
    proposal_sent: 'default',
    won: 'default',
    lost: 'destructive',
    archived: 'outline',
};

defineOptions({
    layout: {
        wide: true,
        breadcrumbs: [
            {
                title: 'Proyectos',
                href: projectsIndex(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Proyectos" />

    <div class="flex flex-col space-y-6">
        <div class="page-header">
            <Heading
                title="Proyectos solares"
                description="Gestiona tus proyectos y su progreso"
            />

            <Link
                href="/projects/create"
                as="button"
                :class="buttonVariants()"
                data-test="create-project-button"
            >
                Nuevo proyecto
            </Link>
        </div>

        <div class="data-table">
            <table class="w-full min-w-[56rem] text-left text-sm">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-4 py-2 font-medium">Código</th>
                        <th class="px-4 py-2 font-medium">Nombre</th>
                        <th class="px-4 py-2 font-medium">Cliente</th>
                        <th class="px-4 py-2 font-medium">Estado</th>
                        <th class="px-4 py-2 font-medium">Fecha</th>
                        <th class="px-4 py-2 font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="project in projects"
                        :key="project.id"
                        class="border-t"
                        :data-test="`project-row-${project.id}`"
                    >
                        <td class="px-4 py-2 font-mono whitespace-nowrap">
                            {{ project.code }}
                        </td>
                        <td class="px-4 py-2">
                            {{ project.name }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            {{ project.client_name }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <Badge :variant="statusColors[project.status]">
                                {{ statusLabels[project.status] }}
                            </Badge>
                        </td>
                        <td
                            class="px-4 py-2 whitespace-nowrap text-muted-foreground"
                        >
                            {{ project.created_at }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="`/projects/${project.id}`"
                                    as="button"
                                    :class="
                                        buttonVariants({
                                            variant: 'outline',
                                            size: 'sm',
                                        })
                                    "
                                >
                                    Ver
                                </Link>
                                <Link
                                    :href="`/projects/${project.id}/edit`"
                                    as="button"
                                    :class="
                                        buttonVariants({
                                            variant: 'outline',
                                            size: 'sm',
                                        })
                                    "
                                >
                                    Editar
                                </Link>
                                <Form
                                    method="DELETE"
                                    :action="`/projects/${project.id}`"
                                    @submit="
                                        !confirmDelete(
                                            '¿Deseas eliminar este proyecto?',
                                        ) && $event.preventDefault()
                                    "
                                >
                                    <Button
                                        type="submit"
                                        size="sm"
                                        variant="ghost"
                                        :data-test="`delete-project-${project.id}`"
                                    >
                                        Eliminar
                                    </Button>
                                </Form>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <PaginationLinks :links="pagination" />
    </div>
</template>
