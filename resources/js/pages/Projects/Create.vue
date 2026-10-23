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
import { index as projectsIndex } from '@/routes/projects';

interface Client {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
}

const page = usePage();
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
                title: 'Crear',
            },
        ],
    },
});
</script>

<template>
    <Head title="Crear proyecto" />

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Crear proyecto"
            description="Crea un nuevo proyecto solar"
        />

        <Form
            v-bind="ProjectController.store.form()"
            class="max-w-2xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="surface-panel">
                <h3 class="mb-4 text-lg font-semibold">Datos generales</h3>
                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="client_id">Cliente</Label>
                        <Select name="client_id" required>
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
                        <Input id="name" name="name" required />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="status">Estado</Label>
                        <Select name="status" default-value="draft">
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
                        <Select name="installation_type" required>
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
                        <Select name="assigned_user_id">
                            <SelectTrigger id="assigned_user_id">
                                <SelectValue
                                    placeholder="Selecciona un usuario"
                                />
                            </SelectTrigger>
                            <SelectContent>
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

            <div class="surface-panel">
                <h3 class="mb-4 text-lg font-semibold">Ubicación</h3>
                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="street">Calle</Label>
                        <Input id="street" name="street" />
                        <InputError :message="errors.street" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="exterior_number">Número exterior</Label>
                        <Input id="exterior_number" name="exterior_number" />
                        <InputError :message="errors.exterior_number" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="neighborhood">Colonia</Label>
                        <Input id="neighborhood" name="neighborhood" />
                        <InputError :message="errors.neighborhood" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="municipality">Municipio</Label>
                        <Input id="municipality" name="municipality" />
                        <InputError :message="errors.municipality" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="state">Estado</Label>
                        <Input id="state" name="state" />
                        <InputError :message="errors.state" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="postal_code">Código postal</Label>
                        <Input id="postal_code" name="postal_code" />
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

            <div class="surface-panel">
                <h3 class="mb-4 text-lg font-semibold">Notas</h3>
                <div class="grid gap-2">
                    <Label for="notes">Notas adicionales</Label>
                    <Textarea id="notes" name="notes" class="min-h-24" />
                    <InputError :message="errors.notes" />
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <Button type="submit" :disabled="processing">
                    Crear proyecto
                </Button>
            </div>
        </Form>
    </div>
</template>
