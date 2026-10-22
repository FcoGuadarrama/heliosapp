<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProjectController from '@/actions/App/Http/Controllers/ProjectController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { AlertCircle } from '@lucide/vue';
import { index as projectsIndex } from '@/routes/projects';

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
    isMissingCoordinates: boolean;
}

interface Client {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
}

const page = usePage();
const project = computed(() => page.props.project as unknown as ProjectData);
const clients = computed(() => page.props.clients as unknown as Client[]);
const users = computed(() => page.props.users as unknown as User[]);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Proyectos',
                href: projectsIndex(),
            },
            {
                title: 'Editar',
            },
        ],
    },
});
</script>

<template>
    <Head title="Editar proyecto" />

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Editar proyecto"
            :description="`${project.code} - ${project.name}`"
        />

        <Form
            v-bind="ProjectController.update.form(project.id)"
            class="max-w-2xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <Alert
                v-if="project.isMissingCoordinates"
                class="border-yellow-200 bg-yellow-50"
            >
                <AlertCircle class="h-4 w-4 text-yellow-600" />
                <AlertDescription class="text-yellow-800">
                    Este proyecto aún no tiene coordenadas definidas. Sin
                    latitud y longitud, no es posible estimar la irradiación
                    solar ni dimensionar el sistema.
                </AlertDescription>
            </Alert>

            <div class="rounded-lg border p-6">
                <h3 class="mb-4 text-lg font-semibold">Datos generales</h3>
                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="client_id">Cliente</Label>
                        <Select
                            name="client_id"
                            :default-value="String(project.client_id)"
                        >
                            <SelectTrigger id="client_id">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="client in clients"
                                    :key="client.id"
                                    :value="String(client.id)"
                                >
                                    {{ client.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.client_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Nombre del proyecto</Label>
                        <Input
                            id="name"
                            name="name"
                            :default-value="project.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="status">Estado</Label>
                        <Select name="status" :default-value="project.status">
                            <SelectTrigger id="status">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="draft">Borrador</SelectItem>
                                <SelectItem value="data_received"
                                    >Datos recibidos</SelectItem
                                >
                                <SelectItem value="analyzing"
                                    >Analizando</SelectItem
                                >
                                <SelectItem value="analyzed"
                                    >Analizado</SelectItem
                                >
                                <SelectItem value="quoted"
                                    >Presupuestado</SelectItem
                                >
                                <SelectItem value="proposal_sent"
                                    >Propuesta enviada</SelectItem
                                >
                                <SelectItem value="won">Ganado</SelectItem>
                                <SelectItem value="lost">Perdido</SelectItem>
                                <SelectItem value="archived"
                                    >Archivado</SelectItem
                                >
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.status" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="installation_type"
                            >Tipo de instalación</Label
                        >
                        <Select
                            name="installation_type"
                            :default-value="project.installation_type"
                        >
                            <SelectTrigger id="installation_type">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="roof">Techo</SelectItem>
                                <SelectItem value="ground">Terreno</SelectItem>
                                <SelectItem value="carport"
                                    >Estacionamiento</SelectItem
                                >
                                <SelectItem value="other">Otro</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.installation_type" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="assigned_user_id">Asignado a</Label>
                        <Select
                            name="assigned_user_id"
                            :default-value="
                                String(project.assigned_user_id || '')
                            "
                        >
                            <SelectTrigger id="assigned_user_id">
                                <SelectValue
                                    placeholder="Selecciona un usuario"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">Sin asignar</SelectItem>
                                <SelectItem
                                    v-for="user in users"
                                    :key="user.id"
                                    :value="String(user.id)"
                                >
                                    {{ user.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.assigned_user_id" />
                    </div>
                </div>
            </div>

            <div class="rounded-lg border p-6">
                <h3 class="mb-4 text-lg font-semibold">Ubicación</h3>
                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="street">Calle</Label>
                        <Input
                            id="street"
                            name="street"
                            :default-value="project.street ?? ''"
                        />
                        <InputError :message="errors.street" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="exterior_number">Número exterior</Label>
                        <Input
                            id="exterior_number"
                            name="exterior_number"
                            :default-value="project.exterior_number ?? ''"
                        />
                        <InputError :message="errors.exterior_number" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="neighborhood">Colonia</Label>
                        <Input
                            id="neighborhood"
                            name="neighborhood"
                            :default-value="project.neighborhood ?? ''"
                        />
                        <InputError :message="errors.neighborhood" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="municipality">Municipio</Label>
                        <Input
                            id="municipality"
                            name="municipality"
                            :default-value="project.municipality ?? ''"
                        />
                        <InputError :message="errors.municipality" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="state">Estado</Label>
                        <Input
                            id="state"
                            name="state"
                            :default-value="project.state ?? ''"
                        />
                        <InputError :message="errors.state" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="postal_code">Código postal</Label>
                        <Input
                            id="postal_code"
                            name="postal_code"
                            :default-value="project.postal_code ?? ''"
                        />
                        <InputError :message="errors.postal_code" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="latitude">Latitud</Label>
                            <Input
                                id="latitude"
                                type="number"
                                step="0.0000001"
                                name="latitude"
                                :default-value="project.latitude ?? ''"
                            />
                            <InputError :message="errors.latitude" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="longitude">Longitud</Label>
                            <Input
                                id="longitude"
                                type="number"
                                step="0.0000001"
                                name="longitude"
                                :default-value="project.longitude ?? ''"
                            />
                            <InputError :message="errors.longitude" />
                        </div>
                    </div>

                    <p class="text-sm text-muted-foreground">
                        Las coordenadas (latitud y longitud) son necesarias para
                        calcular la irradiación solar y dimensionar el sistema.
                    </p>
                </div>
            </div>

            <div class="rounded-lg border p-6">
                <h3 class="mb-4 text-lg font-semibold">Notas</h3>
                <div class="grid gap-2">
                    <Label for="notes">Notas adicionales</Label>
                    <Textarea
                        id="notes"
                        name="notes"
                        :default-value="project.notes ?? ''"
                        class="min-h-24"
                    />
                    <InputError :message="errors.notes" />
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <Button type="submit" :disabled="processing">
                    Guardar cambios
                </Button>
            </div>
        </Form>
    </div>
</template>
