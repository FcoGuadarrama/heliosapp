<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { AlertCircle } from '@lucide/vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { show as projectsShow } from '@/routes/projects';

interface ProjectData {
    id: number;
    code: string;
    client_id: number;
    name: string;
    status: string;
    installation_type: string;
    property_type: string | null;
    street: string | null;
    exterior_number: string | null;
    neighborhood: string | null;
    municipality: string | null;
    state: string | null;
    postal_code: string | null;
    latitude: string | null;
    longitude: string | null;
    objectives: Record<string, unknown> | null;
    property_data: Record<string, unknown> | null;
    notes: string | null;
    assigned_user_id: number | null;
    created_at: string;
    isMissingCoordinates: boolean;
}

const page = usePage();
const project = computed(() => page.props.project as unknown as ProjectData);
const clients = computed(
    () => page.props.clients as unknown as Array<{ id: number; name: string }>,
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

const installationLabels: Record<string, string> = {
    roof: 'Techo',
    ground: 'Terreno',
    carport: 'Estacionamiento',
    other: 'Otro',
};

const clientName = computed(() => {
    const client = clients.value.find((c) => c.id === project.value.client_id);
    return client?.name || 'Desconocido';
});

const typeLabel = (type: string) => {
    const installationLabels: Record<string, string> = {
        roof: 'Techo',
        ground: 'Terreno',
        carport: 'Estacionamiento',
        other: 'Otro',
    };
    return installationLabels[type] || type;
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Proyectos',
                href: '/projects',
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Proyecto - ${project.code}`" />

    <div class="flex flex-col space-y-6">
        <div class="flex items-center justify-between">
            <Heading
                variant="small"
                :title="project.code"
                :description="project.name"
            />

            <Link
                :href="`/projects/${project.id}/edit`"
                as="button"
                class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-8 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
            >
                Editar proyecto
            </Link>
        </div>

        <Alert
            v-if="project.isMissingCoordinates"
            class="border-yellow-200 bg-yellow-50"
        >
            <AlertCircle class="h-4 w-4 text-yellow-600" />
            <AlertDescription class="text-yellow-800">
                Este proyecto aún no tiene coordenadas definidas. Sin latitud y
                longitud, no es posible estimar la irradiación solar ni
                dimensionar el sistema. Por favor, proporciona las coordenadas
                del sitio.
            </AlertDescription>
        </Alert>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">Estado</p>
                <Badge class="mt-2">
                    {{ statusLabels[project.status] }}
                </Badge>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">Tipo de instalación</p>
                <p class="mt-2 font-medium">
                    {{ installationLabels[project.installation_type] }}
                </p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">Cliente</p>
                <p class="mt-2 font-medium">{{ clientName }}</p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">Creado</p>
                <p class="mt-2 font-medium">{{ project.created_at }}</p>
            </div>
        </div>

        <div class="rounded-lg border p-6">
            <h3 class="mb-4 text-lg font-semibold">Información del sitio</h3>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <p class="text-sm text-muted-foreground">Calle</p>
                    <p class="font-medium">{{ project.street || '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Número exterior</p>
                    <p class="font-medium">
                        {{ project.exterior_number || '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Colonia</p>
                    <p class="font-medium">{{ project.neighborhood || '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Municipio</p>
                    <p class="font-medium">{{ project.municipality || '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Estado</p>
                    <p class="font-medium">{{ project.state || '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Código postal</p>
                    <p class="font-medium">{{ project.postal_code || '-' }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-lg border p-6">
            <h3 class="mb-4 text-lg font-semibold">Coordenadas</h3>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <p class="text-sm text-muted-foreground">Latitud</p>
                    <p class="font-mono font-medium">
                        {{ project.latitude || 'No definida' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Longitud</p>
                    <p class="font-mono font-medium">
                        {{ project.longitude || 'No definida' }}
                    </p>
                </div>
            </div>
        </div>

        <div v-if="project.notes" class="rounded-lg border p-6">
            <h3 class="mb-4 text-lg font-semibold">Notas</h3>
            <p class="text-sm">{{ project.notes }}</p>
        </div>

        <div class="rounded-lg border p-6">
            <h3 class="mb-4 text-lg font-semibold">Datos energéticos</h3>
            <p class="text-sm text-muted-foreground">
                Los datos energéticos se habilitarán después de importar el
                recibo de electricidad (CFE). Una vez cargado, podrás ver el
                historial de consumo, el análisis energético y las
                recomendaciones de dimensionamiento del sistema.
            </p>
        </div>
    </div>
</template>
