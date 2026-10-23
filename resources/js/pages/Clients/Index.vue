<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const confirmDelete = (message: string): boolean => {
    return window.confirm(message);
};
import ClientController from '@/actions/App/Http/Controllers/ClientController';
import Heading from '@/components/Heading.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { create as intakeCreate } from '@/routes/intakes';
import { index as clientsIndex } from '@/routes/clients';

interface Client {
    id: number;
    type: string | null;
    name: string;
    email: string | null;
    phone: string | null;
    municipality: string | null;
}

interface PaginatedClients {
    data: Client[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

defineOptions({
    layout: {
        wide: true,
        breadcrumbs: [
            {
                title: 'Mis prospectos',
                href: clientsIndex(),
            },
        ],
    },
});

const page = usePage();
const permissions = computed(() => page.props.auth.permissions ?? []);
const clients = computed(
    () => (page.props.clients as unknown as PaginatedClients).data,
);
const pagination = computed(
    () => (page.props.clients as unknown as PaginatedClients).links,
);
const searchQuery = computed(() => page.props.search as unknown as string);

const typeLabels: Record<string, string> = {
    residential: 'Residencial',
    commercial: 'Comercial',
    industrial: 'Industrial',
    agricultural: 'Agrícola',
};

const typeColors: Record<
    string,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    residential: 'default',
    commercial: 'secondary',
    industrial: 'destructive',
    agricultural: 'outline',
};

const createOpen = ref(false);
</script>

<template>
    <Head title="Mis prospectos" />

    <div class="flex flex-col space-y-6">
        <div class="page-header">
            <Heading
                title="Mis prospectos"
                description="Gestiona tus clientes y sus datos de contacto"
            />

            <div class="flex flex-wrap gap-2">
                <Button
                    v-if="
                        page.props.auth.permissions.includes('receipts.import')
                    "
                    as-child
                    ><Link :href="intakeCreate()">Recibir CFE</Link></Button
                >
                <Dialog
                    v-if="permissions.includes('clients.create')"
                    v-model:open="createOpen"
                >
                    <DialogTrigger as-child>
                        <Button data-test="create-client-button"
                            >Nuevo prospecto</Button
                        >
                    </DialogTrigger>
                    <DialogContent>
                        <Form
                            v-bind="ClientController.store.form()"
                            reset-on-success
                            :options="{ preserveScroll: true }"
                            @success="createOpen = false"
                            class="space-y-4"
                            v-slot="{ errors, processing }"
                        >
                            <DialogHeader>
                                <DialogTitle>Nuevo prospecto</DialogTitle>
                                <DialogDescription>
                                    Crea un nuevo cliente y su información de
                                    contacto.
                                </DialogDescription>
                            </DialogHeader>

                            <div class="grid gap-2">
                                <Label for="create-type">Tipo</Label>
                                <Select name="type" required>
                                    <SelectTrigger id="create-type">
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
                                <Label for="create-name">Nombre</Label>
                                <Input id="create-name" name="name" required />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="create-contact"
                                    >Nombre de contacto</Label
                                >
                                <Input
                                    id="create-contact"
                                    name="contact_name"
                                />
                                <InputError :message="errors.contact_name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="create-email">Correo</Label>
                                <Input
                                    id="create-email"
                                    type="email"
                                    name="email"
                                />
                                <InputError :message="errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="create-phone">Teléfono</Label>
                                <Input
                                    id="create-phone"
                                    type="tel"
                                    name="phone"
                                />
                                <InputError :message="errors.phone" />
                            </div>

                            <div class="flex justify-end gap-2">
                                <Button type="submit" :disabled="processing">
                                    Crear prospecto
                                </Button>
                            </div>
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>
        </div>

        <Form
            v-bind="ClientController.index.form()"
            class="flex flex-wrap items-end gap-2"
            role="search"
        >
            <div class="grid w-full max-w-md gap-2">
                <Label for="search">Buscar prospectos</Label>
                <Input
                    id="search"
                    name="search"
                    type="search"
                    placeholder="Nombre, correo o teléfono"
                    :default-value="searchQuery"
                />
            </div>
            <Button type="submit" variant="outline">Buscar</Button>
            <Link
                v-if="searchQuery"
                :href="clientsIndex()"
                class="px-3 py-2 text-sm underline"
                >Limpiar búsqueda</Link
            >
        </Form>

        <p v-if="clients.length === 0" class="text-sm text-muted-foreground">
            No hay prospectos que mostrar. Crea un prospecto o cambia la
            búsqueda.
        </p>

        <div class="data-table">
            <table class="w-full min-w-[56rem] text-left text-sm">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-4 py-2 font-medium">Nombre</th>
                        <th class="px-4 py-2 font-medium">Tipo</th>
                        <th class="px-4 py-2 font-medium">Correo</th>
                        <th class="px-4 py-2 font-medium">Teléfono</th>
                        <th class="px-4 py-2 font-medium">Municipio</th>
                        <th class="px-4 py-2 font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="client in clients"
                        :key="client.id"
                        class="border-t"
                        :data-test="`client-row-${client.id}`"
                    >
                        <td class="px-4 py-2 whitespace-nowrap">
                            {{ client.name }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <Badge
                                :variant="
                                    typeColors[client.type ?? ''] ?? 'outline'
                                "
                            >
                                {{
                                    typeLabels[client.type ?? ''] ??
                                    'Sin clasificar'
                                }}
                            </Badge>
                        </td>
                        <td
                            class="px-4 py-2 whitespace-nowrap text-muted-foreground"
                        >
                            {{ client.email || '-' }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            {{ client.phone || '-' }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            {{ client.municipality || '-' }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="`/clients/${client.id}`"
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
                                    v-if="
                                        permissions.includes('clients.update')
                                    "
                                    :href="`/clients/${client.id}/edit`"
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
                                    v-if="
                                        permissions.includes('clients.delete')
                                    "
                                    method="DELETE"
                                    :action="`/clients/${client.id}`"
                                    @submit="
                                        !confirmDelete(
                                            '¿Deseas eliminar este cliente?',
                                        ) && $event.preventDefault()
                                    "
                                >
                                    <Button
                                        type="submit"
                                        size="sm"
                                        variant="ghost"
                                        :data-test="`delete-client-${client.id}`"
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
