<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const confirmDelete = (message: string): boolean => {
    return window.confirm(message);
};
import ClientController from '@/actions/App/Http/Controllers/ClientController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
import { index as clientsIndex } from '@/routes/clients';

interface Client {
    id: number;
    type: string;
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
                title: 'Clientes',
                href: clientsIndex(),
            },
        ],
    },
});

const page = usePage();
const clients = computed(() => (page.props.clients as unknown as PaginatedClients).data);
const searchQuery = computed(() => page.props.search as unknown as string);

const typeLabels: Record<string, string> = {
    residential: 'Residencial',
    commercial: 'Comercial',
    industrial: 'Industrial',
    agricultural: 'Agrícola',
};

const typeColors: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    residential: 'default',
    commercial: 'secondary',
    industrial: 'destructive',
    agricultural: 'outline',
};

const createOpen = ref(false);
</script>

<template>
    <Head title="Clientes" />

    <div class="flex flex-col space-y-6">
        <div class="flex items-center justify-between">
            <Heading
                variant="small"
                title="Clientes"
                description="Gestiona tus clientes y sus datos de contacto"
            />

            <Dialog v-model:open="createOpen">
                <DialogTrigger as-child>
                    <Button data-test="create-client-button">Nuevo cliente</Button>
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
                            <DialogTitle>Nuevo cliente</DialogTitle>
                            <DialogDescription>
                                Crea un nuevo cliente y su información de contacto.
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
                            <Input
                                id="create-name"
                                name="name"
                                required
                            />
                            <InputError :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-contact">Nombre de contacto</Label>
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
                                Crear cliente
                            </Button>
                        </div>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>

        <div class="flex items-center gap-2">
            <Input
                type="search"
                placeholder="Buscar por nombre, correo o teléfono..."
                :default-value="searchQuery"
                class="max-w-md"
                @change="$event.target.form?.submit()"
                @keydown.enter="$event.target.form?.submit()"
            />
            <form method="GET" action="#" class="hidden">
                <input type="hidden" name="search" :value="searchQuery" />
            </form>
        </div>

        <div class="w-full overflow-x-auto rounded-lg border">
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
                            <Badge :variant="typeColors[client.type]">
                                {{ typeLabels[client.type] }}
                            </Badge>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-muted-foreground">
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
                                    class="inline-flex h-9 items-center justify-center rounded-md border border-input bg-background px-3 text-sm font-medium shadow-sm hover:bg-accent hover:text-accent-foreground"
                                >
                                    Ver
                                </Link>
                                <Link
                                    :href="`/clients/${client.id}/edit`"
                                    as="button"
                                    class="inline-flex h-9 items-center justify-center rounded-md border border-input bg-background px-3 text-sm font-medium shadow-sm hover:bg-accent hover:text-accent-foreground"
                                >
                                    Editar
                                </Link>
                                <Form
                                    method="DELETE"
                                    :action="`/clients/${client.id}`"
                                    @submit="!confirmDelete('¿Deseas eliminar este cliente?') && $event.preventDefault()"
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
    </div>
</template>
