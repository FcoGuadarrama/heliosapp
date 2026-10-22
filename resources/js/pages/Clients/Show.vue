<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import IntakeController from '@/actions/App/Http/Controllers/IntakeController';
import { create as intakeCreate, file as intakeFile } from '@/routes/intakes';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    create as projectsCreate,
    show as projectsShow,
} from '@/routes/projects';
import { index as clientsIndex } from '@/routes/clients';

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

interface Intake {
    id: number;
    status: string;
    mime_type: string;
    created_at: string;
    reader_identifier: string | null;
    fixture_identifier: string | null;
    validation_result: Record<string, unknown> | null;
}

const page = usePage();
const permissions = computed(() => page.props.auth.permissions ?? []);
const intakes = computed(() => (page.props.intakes ?? []) as Intake[]);
const receiptStatuses: Record<string, string> = {
    stored: 'Archivo guardado · extracción pendiente',
    mock_validated: 'Simulación: JSON validado',
    mock_needs_review: 'Simulación: requiere revisión',
    mock_rejected: 'Simulación: JSON rechazado',
};
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
                title: 'Mis prospectos',
                href: clientsIndex(),
            },
            {
                title: 'Detalle',
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Prospecto - ${client.name}`" />

    <div class="flex flex-col space-y-6">
        <div class="flex items-center justify-between">
            <Heading
                variant="small"
                :title="client.name"
                :description="typeLabels[client.type]"
            />

            <Link
                v-if="permissions.includes('clients.update')"
                :href="`/clients/${client.id}/edit`"
                as="button"
                class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-8 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
            >
                Editar prospecto
            </Link>
        </div>

        <section
            class="space-y-4 rounded-lg border p-6"
            aria-labelledby="receipts-heading"
        >
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="receipts-heading" class="text-lg font-semibold">
                    Recibos CFE
                </h2>
                <Button
                    v-if="
                        permissions.includes('receipts.import') &&
                        permissions.includes('clients.view')
                    "
                    as-child
                    variant="outline"
                    ><Link
                        :href="
                            intakeCreate({ query: { client_id: client.id } })
                        "
                        >Adjuntar recibo</Link
                    ></Button
                >
            </div>
            <p class="text-sm text-muted-foreground">
                Los archivos se vinculan directamente a este prospecto. No se
                requiere proyecto. La extracción automática está pendiente.
            </p>
            <p
                v-if="!permissions.includes('receipts.view')"
                class="text-sm text-muted-foreground"
            >
                No tienes permiso para consultar recibos.
            </p>
            <p
                v-else-if="intakes.length === 0"
                class="text-sm text-muted-foreground"
            >
                Este prospecto aún no tiene recibos.
            </p>
            <article
                v-for="intake in intakes"
                :key="intake.id"
                class="space-y-3 border-t pt-4"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="font-medium">Recibo #{{ intake.id }}</h3>
                        <p class="text-sm text-muted-foreground">
                            {{ intake.created_at }} · {{ intake.mime_type }}
                        </p>
                    </div>
                    <a
                        :href="intakeFile(intake.id).url"
                        class="text-sm underline"
                        >Descargar recibo #{{ intake.id }}</a
                    >
                </div>
                <Badge variant="secondary">{{
                    receiptStatuses[intake.status] ?? intake.status
                }}</Badge>
                <div
                    v-if="intake.reader_identifier"
                    class="space-y-2 rounded-md border p-4"
                >
                    <p class="text-sm font-medium">
                        Simulación con datos ficticios
                    </p>
                    <p class="text-sm text-muted-foreground">
                        Fixture: {{ intake.fixture_identifier }}. Estos datos no
                        fueron extraídos del PDF o imagen. No son aptos para
                        emitir una cotización ni un dimensionamiento.
                    </p>
                    <details v-if="intake.validation_result">
                        <summary class="cursor-pointer text-sm underline">
                            Ver resultado de validación del JSON de prueba
                        </summary>
                        <pre
                            class="mt-3 max-h-96 overflow-auto rounded bg-muted p-3 text-xs"
                            >{{
                                JSON.stringify(
                                    intake.validation_result,
                                    null,
                                    2,
                                )
                            }}</pre>
                    </details>
                </div>
                <details
                    v-else-if="permissions.includes('receipts.import')"
                    class="rounded-md border p-4"
                >
                    <summary class="cursor-pointer text-sm">
                        Probar validación con datos ficticios
                    </summary>
                    <p class="my-3 text-sm text-muted-foreground">
                        Registra una simulación única con un fixture JSON. No
                        lee ni extrae información del archivo adjunto.
                    </p>
                    <Form
                        v-bind="IntakeController.mock.form(intake.id)"
                        v-slot="{ errors, processing }"
                        class="space-y-3"
                    >
                        <label
                            :for="`fixture-${intake.id}`"
                            class="block text-sm font-medium"
                            >Escenario de prueba</label
                        >
                        <select
                            :id="`fixture-${intake.id}`"
                            name="fixture_identifier"
                            class="h-10 w-full rounded-md border bg-background px-3 text-sm"
                            required
                        >
                            <option value="complete">Completo</option>
                            <option value="partial">Parcial</option>
                            <option value="incoherent">Incoherente</option>
                            <option value="malformed">Malformado</option>
                        </select>
                        <InputError :message="errors.fixture_identifier" />
                        <Button
                            type="submit"
                            variant="outline"
                            :disabled="processing"
                            >Validar fixture de prueba</Button
                        >
                    </Form>
                </details>
            </article>
        </section>

        <div class="grid gap-6">
            <div class="rounded-lg border p-6">
                <h3 class="mb-4 text-lg font-semibold">Información general</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Nombre de contacto
                        </p>
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
                        <p class="text-sm text-muted-foreground">
                            Número exterior
                        </p>
                        <p class="font-medium">
                            {{ client.exterior_number || '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Número interior
                        </p>
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
                        <p class="text-sm text-muted-foreground">
                            Código postal
                        </p>
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

            <div
                v-if="permissions.includes('projects.view')"
                class="rounded-lg border p-6"
            >
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">
                        Proyectos internos (opcionales)
                    </h3>
                    <Link
                        v-if="permissions.includes('projects.create')"
                        :href="projectsCreate()"
                        as="button"
                        class="inline-flex h-9 items-center justify-center rounded-md border border-input bg-background px-3 text-sm font-medium shadow-sm hover:bg-accent"
                    >
                        Nuevo proyecto
                    </Link>
                </div>
                <div class="mt-4">
                    <div
                        v-if="projects.length === 0"
                        class="text-center text-muted-foreground"
                    >
                        <p>No hay proyectos para este cliente</p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium">
                                        Código
                                    </th>
                                    <th class="px-4 py-2 text-left font-medium">
                                        Nombre
                                    </th>
                                    <th class="px-4 py-2 text-left font-medium">
                                        Estado
                                    </th>
                                    <th class="px-4 py-2 text-left font-medium">
                                        Fecha
                                    </th>
                                    <th class="px-4 py-2 text-left font-medium">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="project in projects"
                                    :key="project.id"
                                    class="border-t"
                                >
                                    <td class="px-4 py-2">
                                        {{ project.code }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ project.name }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <Badge variant="secondary">
                                            {{ statusLabels[project.status] }}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ project.created_at }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <Link
                                            :href="projectsShow(project.id)"
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
