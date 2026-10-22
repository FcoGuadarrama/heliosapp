<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import UserController from '@/actions/App/Http/Controllers/Settings/UserController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index as usersIndex } from '@/routes/users';

interface ManagedUser {
    id: number;
    name: string;
    email: string;
    role: string | null;
    is_active: boolean;
}

defineOptions({
    layout: {
        // Tabla de datos: necesita el ancho completo, no la columna de formulario.
        wide: true,
        breadcrumbs: [
            {
                title: 'Usuarios',
                href: usersIndex(),
            },
        ],
    },
});

const page = usePage();
const users = computed(() => page.props.users as unknown as ManagedUser[]);
const roles = computed(() => page.props.roles as unknown as string[]);
const currentUserId = computed(() => page.props.auth.user?.id);

const editingUser = ref<ManagedUser | null>(null);
const createOpen = ref(false);

const roleLabels: Record<string, string> = {
    owner: 'Propietario',
    admin: 'Administrador',
    engineer: 'Ingeniero',
    sales: 'Ventas',
    viewer: 'Solo lectura',
};

const roleLabel = (role: string | null) =>
    role ? roleLabels[role] ?? role : 'Sin rol';
</script>

<template>
    <Head title="Usuarios" />

    <div class="flex flex-col space-y-6">
        <div class="flex items-center justify-between">
            <Heading
                variant="small"
                title="Usuarios"
                description="Gestiona el acceso de las personas que usan Helios en tu empresa"
            />

            <Dialog v-model:open="createOpen">
                <DialogTrigger as-child>
                    <Button data-test="create-user-button">Nuevo usuario</Button>
                </DialogTrigger>
                <DialogContent>
                    <Form
                        v-bind="UserController.store.form()"
                        reset-on-success
                        :options="{ preserveScroll: true }"
                        @success="createOpen = false"
                        class="space-y-4"
                        v-slot="{ errors, processing }"
                    >
                        <DialogHeader>
                            <DialogTitle>Nuevo usuario</DialogTitle>
                            <DialogDescription>
                                Crea una cuenta y asígnale un rol.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="create-name">Nombre</Label>
                            <Input id="create-name" name="name" required />
                            <InputError :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-email">Correo electrónico</Label>
                            <Input
                                id="create-email"
                                type="email"
                                name="email"
                                required
                            />
                            <InputError :message="errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-password">Contraseña</Label>
                            <Input
                                id="create-password"
                                type="password"
                                name="password"
                                required
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-password-confirmation"
                                >Confirmar contraseña</Label
                            >
                            <Input
                                id="create-password-confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-role">Rol</Label>
                            <Select name="role" default-value="viewer">
                                <SelectTrigger id="create-role">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="roleOption in roles"
                                        :key="roleOption"
                                        :value="roleOption"
                                    >
                                        {{ roleLabel(roleOption) }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.role" />
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button type="submit" :disabled="processing">
                                Crear usuario
                            </Button>
                        </div>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>

        <div class="w-full overflow-x-auto rounded-lg border">
            <table class="w-full min-w-[46rem] text-left text-sm">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-4 py-2 font-medium">Nombre</th>
                        <th class="px-4 py-2 font-medium">Correo</th>
                        <th class="px-4 py-2 font-medium">Rol</th>
                        <th class="px-4 py-2 font-medium">Estado</th>
                        <th class="px-4 py-2 font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="managedUser in users"
                        :key="managedUser.id"
                        class="border-t"
                        :data-test="`user-row-${managedUser.id}`"
                    >
                        <td class="px-4 py-2 whitespace-nowrap">
                            {{ managedUser.name }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            {{ managedUser.email }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            {{ roleLabel(managedUser.role) }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <Badge
                                :variant="
                                    managedUser.is_active
                                        ? 'default'
                                        : 'secondary'
                                "
                            >
                                {{
                                    managedUser.is_active
                                        ? 'Activo'
                                        : 'Inactivo'
                                }}
                            </Badge>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <Dialog
                                    :open="editingUser?.id === managedUser.id"
                                    @update:open="
                                        (open: boolean) =>
                                            (editingUser = open
                                                ? managedUser
                                                : null)
                                    "
                                >
                                    <DialogTrigger as-child>
                                        <Button variant="outline" size="sm">
                                            Editar
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <Form
                                            v-bind="
                                                UserController.update.form(
                                                    managedUser.id,
                                                )
                                            "
                                            :options="{
                                                preserveScroll: true,
                                            }"
                                            @success="editingUser = null"
                                            class="space-y-4"
                                            v-slot="{ errors, processing }"
                                        >
                                            <DialogHeader>
                                                <DialogTitle
                                                    >Editar usuario</DialogTitle
                                                >
                                            </DialogHeader>

                                            <div class="grid gap-2">
                                                <Label
                                                    :for="`edit-name-${managedUser.id}`"
                                                    >Nombre</Label
                                                >
                                                <Input
                                                    :id="`edit-name-${managedUser.id}`"
                                                    name="name"
                                                    required
                                                    :default-value="
                                                        managedUser.name
                                                    "
                                                />
                                                <InputError
                                                    :message="errors.name"
                                                />
                                            </div>

                                            <div class="grid gap-2">
                                                <Label
                                                    :for="`edit-email-${managedUser.id}`"
                                                    >Correo electrónico</Label
                                                >
                                                <Input
                                                    :id="`edit-email-${managedUser.id}`"
                                                    type="email"
                                                    name="email"
                                                    required
                                                    :default-value="
                                                        managedUser.email
                                                    "
                                                />
                                                <InputError
                                                    :message="errors.email"
                                                />
                                            </div>

                                            <div class="grid gap-2">
                                                <Label
                                                    :for="`edit-role-${managedUser.id}`"
                                                    >Rol</Label
                                                >

                                                <template
                                                    v-if="
                                                        managedUser.id ===
                                                        currentUserId
                                                    "
                                                >
                                                    <input
                                                        type="hidden"
                                                        name="role"
                                                        :value="
                                                            managedUser.role ??
                                                            'viewer'
                                                        "
                                                    />
                                                    <p
                                                        :id="`edit-role-${managedUser.id}`"
                                                        class="rounded-md border px-3 py-2 text-sm text-muted-foreground"
                                                    >
                                                        {{
                                                            roleLabel(
                                                                managedUser.role,
                                                            )
                                                        }}
                                                    </p>
                                                    <p
                                                        class="text-xs text-muted-foreground"
                                                    >
                                                        No puedes cambiar tu
                                                        propio rol.
                                                    </p>
                                                </template>

                                                <Select
                                                    v-else
                                                    name="role"
                                                    :default-value="
                                                        managedUser.role ??
                                                        'viewer'
                                                    "
                                                >
                                                    <SelectTrigger
                                                        :id="`edit-role-${managedUser.id}`"
                                                    >
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem
                                                            v-for="roleOption in roles"
                                                            :key="roleOption"
                                                            :value="
                                                                roleOption
                                                            "
                                                        >
                                                            {{
                                                                roleLabel(
                                                                    roleOption,
                                                                )
                                                            }}
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                                <InputError
                                                    :message="errors.role"
                                                />
                                            </div>

                                            <div
                                                class="flex justify-end gap-2"
                                            >
                                                <Button
                                                    type="submit"
                                                    :disabled="processing"
                                                >
                                                    Guardar cambios
                                                </Button>
                                            </div>
                                        </Form>
                                    </DialogContent>
                                </Dialog>

                                <Form
                                    v-bind="
                                        UserController.toggleActive.form(
                                            managedUser.id,
                                        )
                                    "
                                    :options="{ preserveScroll: true }"
                                >
                                    <Button
                                        type="submit"
                                        size="sm"
                                        variant="ghost"
                                        :disabled="
                                            managedUser.id === currentUserId
                                        "
                                        :data-test="`toggle-active-${managedUser.id}`"
                                    >
                                        {{
                                            managedUser.is_active
                                                ? 'Desactivar'
                                                : 'Activar'
                                        }}
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
