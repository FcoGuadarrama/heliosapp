<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ClientController from '@/actions/App/Http/Controllers/ClientController';
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

const page = usePage();
const client = computed(() => page.props.client as unknown as ClientData);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Clientes',
                href: clientsIndex(),
            },
            {
                title: 'Editar',
            },
        ],
    },
});
</script>

<template>
    <Head title="Editar cliente" />

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Editar cliente"
            description="Actualiza la información del cliente"
        />

        <Form
            v-bind="ClientController.update.form(client.id)"
            class="max-w-2xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="rounded-lg border p-6">
                <h3 class="mb-4 text-lg font-semibold">Información general</h3>
                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="type">Tipo</Label>
                        <Select name="type" :default-value="client.type">
                            <SelectTrigger id="type">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="residential">
                                    Residencial
                                </SelectItem>
                                <SelectItem value="commercial">
                                    Comercial
                                </SelectItem>
                                <SelectItem value="industrial">
                                    Industrial
                                </SelectItem>
                                <SelectItem value="agricultural">
                                    Agrícola
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.type" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Nombre</Label>
                        <Input
                            id="name"
                            name="name"
                            :default-value="client.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="contact_name">Nombre de contacto</Label>
                        <Input
                            id="contact_name"
                            name="contact_name"
                            :default-value="client.contact_name ?? ''"
                        />
                        <InputError :message="errors.contact_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Correo</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            :default-value="client.email ?? ''"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">Teléfono</Label>
                        <Input
                            id="phone"
                            type="tel"
                            name="phone"
                            :default-value="client.phone ?? ''"
                        />
                        <InputError :message="errors.phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="rfc">RFC</Label>
                        <Input
                            id="rfc"
                            name="rfc"
                            :default-value="client.rfc ?? ''"
                        />
                        <InputError :message="errors.rfc" />
                    </div>
                </div>
            </div>

            <div class="rounded-lg border p-6">
                <h3 class="mb-4 text-lg font-semibold">Dirección</h3>
                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="street">Calle</Label>
                        <Input
                            id="street"
                            name="street"
                            :default-value="client.street ?? ''"
                        />
                        <InputError :message="errors.street" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="exterior_number">Número exterior</Label>
                            <Input
                                id="exterior_number"
                                name="exterior_number"
                                :default-value="client.exterior_number ?? ''"
                            />
                            <InputError :message="errors.exterior_number" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="interior_number">Número interior</Label>
                            <Input
                                id="interior_number"
                                name="interior_number"
                                :default-value="client.interior_number ?? ''"
                            />
                            <InputError :message="errors.interior_number" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="neighborhood">Colonia</Label>
                        <Input
                            id="neighborhood"
                            name="neighborhood"
                            :default-value="client.neighborhood ?? ''"
                        />
                        <InputError :message="errors.neighborhood" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="municipality">Municipio</Label>
                        <Input
                            id="municipality"
                            name="municipality"
                            :default-value="client.municipality ?? ''"
                        />
                        <InputError :message="errors.municipality" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="state">Estado</Label>
                        <Input
                            id="state"
                            name="state"
                            :default-value="client.state ?? ''"
                        />
                        <InputError :message="errors.state" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="postal_code">Código postal</Label>
                        <Input
                            id="postal_code"
                            name="postal_code"
                            :default-value="client.postal_code ?? ''"
                        />
                        <InputError :message="errors.postal_code" />
                    </div>
                </div>
            </div>

            <div class="rounded-lg border p-6">
                <h3 class="mb-4 text-lg font-semibold">Notas</h3>
                <div class="grid gap-2">
                    <Label for="notes">Notas adicionales</Label>
                    <Textarea
                        id="notes"
                        name="notes"
                        :default-value="client.notes ?? ''"
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
