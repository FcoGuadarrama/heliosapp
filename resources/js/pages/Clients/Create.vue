<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
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

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Mis prospectos',
                href: clientsIndex(),
            },
            {
                title: 'Crear',
            },
        ],
    },
});
</script>

<template>
    <Head title="Crear prospecto" />

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Crear prospecto"
            description="Crea un nuevo prospecto y su información de contacto"
        />

        <Form
            v-bind="ClientController.store.form()"
            class="max-w-2xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="rounded-lg border p-6">
                <h3 class="mb-4 text-lg font-semibold">Información general</h3>
                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="type">Tipo</Label>
                        <Select name="type" required>
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
                        <Input id="name" name="name" required />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="contact_name">Nombre de contacto</Label>
                        <Input id="contact_name" name="contact_name" />
                        <InputError :message="errors.contact_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Correo</Label>
                        <Input id="email" type="email" name="email" />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">Teléfono</Label>
                        <Input id="phone" type="tel" name="phone" />
                        <InputError :message="errors.phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="rfc">RFC</Label>
                        <Input id="rfc" name="rfc" />
                        <InputError :message="errors.rfc" />
                    </div>
                </div>
            </div>

            <div class="rounded-lg border p-6">
                <h3 class="mb-4 text-lg font-semibold">Dirección</h3>
                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="street">Calle</Label>
                        <Input id="street" name="street" />
                        <InputError :message="errors.street" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="exterior_number">Número exterior</Label>
                            <Input
                                id="exterior_number"
                                name="exterior_number"
                            />
                            <InputError :message="errors.exterior_number" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="interior_number">Número interior</Label>
                            <Input
                                id="interior_number"
                                name="interior_number"
                            />
                            <InputError :message="errors.interior_number" />
                        </div>
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
                </div>
            </div>

            <div class="rounded-lg border p-6">
                <h3 class="mb-4 text-lg font-semibold">Notas</h3>
                <div class="grid gap-2">
                    <Label for="notes">Notas adicionales</Label>
                    <Textarea id="notes" name="notes" class="min-h-24" />
                    <InputError :message="errors.notes" />
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <Button type="submit" :disabled="processing">
                    Crear prospecto
                </Button>
            </div>
        </Form>
    </div>
</template>
