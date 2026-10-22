<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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
import CompanyController from '@/actions/App/Http/Controllers/Settings/CompanyController';
import { edit } from '@/routes/company';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Configuración de empresa',
                href: edit(),
            },
        ],
    },
});

interface CompanySettings {
    company_name: string | null;
    legal_name: string | null;
    rfc: string | null;
    logo_path: string | null;
    address: {
        street?: string | null;
        number?: string | null;
        interior_number?: string | null;
        neighborhood?: string | null;
        municipality?: string | null;
        state?: string | null;
        postal_code?: string | null;
    } | null;
    phone: string | null;
    email: string | null;
    website: string | null;
    primary_color: string | null;
    secondary_color: string | null;
    timezone: string;
    default_currency: string;
    proposal_validity_days: number;
    responsible_name: string | null;
    responsible_title: string | null;
}

const page = usePage();
const settings = computed(
    () => page.props.settings as unknown as CompanySettings,
);
const timezones = computed(() => [
    'America/Mexico_City',
    'America/New_York',
    'America/Los_Angeles',
    'America/Denver',
    'America/Toronto',
    'America/Vancouver',
    'Europe/London',
    'Europe/Paris',
    'Europe/Madrid',
]);
const currencies = computed(() => [
    { code: 'MXN', name: 'Peso mexicano' },
    { code: 'USD', name: 'Dólar estadounidense' },
    { code: 'EUR', name: 'Euro' },
]);
const logoPreview = ref<string | null>(null);

const handleLogoChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (event) => {
            logoPreview.value = event.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};
</script>

<template>
    <Head title="Configuración de empresa" />

    <h1 class="sr-only">Configuración de empresa</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Configuración de empresa"
            description="Actualiza los datos de tu empresa y preferencias"
        />

        <Form
            v-bind="CompanyController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <!-- Información básica de la empresa -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold">Información básica</h2>

                <div class="grid gap-2">
                    <Label for="company_name">Nombre de la empresa</Label>
                    <Input
                        id="company_name"
                        type="text"
                        class="mt-1 block w-full"
                        name="company_name"
                        :default-value="settings.company_name ?? ''"
                        placeholder="Ej. Mi Empresa Solar"
                    />
                    <InputError class="mt-2" :message="errors.company_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="legal_name">Razón social</Label>
                    <Input
                        id="legal_name"
                        type="text"
                        class="mt-1 block w-full"
                        name="legal_name"
                        :default-value="settings.legal_name ?? ''"
                        placeholder="Ej. Mi Empresa Solar S.A. de C.V."
                    />
                    <InputError class="mt-2" :message="errors.legal_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="rfc">RFC</Label>
                    <Input
                        id="rfc"
                        type="text"
                        class="mt-1 block w-full"
                        name="rfc"
                        :default-value="settings.rfc ?? ''"
                        placeholder="Ej. ABC123456XYZ"
                        maxlength="13"
                    />
                    <InputError class="mt-2" :message="errors.rfc" />
                </div>
            </div>

            <!-- Contacto e identidad visual -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold">Contacto e identidad</h2>

                <div class="grid gap-2">
                    <Label for="phone">Teléfono</Label>
                    <Input
                        id="phone"
                        type="tel"
                        class="mt-1 block w-full"
                        name="phone"
                        :default-value="settings.phone ?? ''"
                        placeholder="Ej. +52 55 1234 5678"
                    />
                    <InputError class="mt-2" :message="errors.phone" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Correo electrónico</Label>
                    <Input
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        name="email"
                        :default-value="settings.email ?? ''"
                        placeholder="Ej. info@empresa.mx"
                    />
                    <InputError class="mt-2" :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="website">Sitio web</Label>
                    <Input
                        id="website"
                        type="url"
                        class="mt-1 block w-full"
                        name="website"
                        :default-value="settings.website ?? ''"
                        placeholder="Ej. https://empresa.mx"
                    />
                    <InputError class="mt-2" :message="errors.website" />
                </div>

                <div class="grid gap-2">
                    <Label for="logo">Logo</Label>
                    <div class="flex items-start gap-4">
                        <div class="flex-1">
                            <Input
                                id="logo"
                                type="file"
                                class="mt-1 block w-full"
                                name="logo"
                                accept="image/jpeg,image/png,image/svg+xml,image/webp"
                                @change="handleLogoChange"
                            />
                            <p class="mt-2 text-sm text-muted-foreground">
                                JPG, PNG, SVG o WebP. Máximo 2 MB.
                            </p>
                            <InputError class="mt-2" :message="errors.logo" />
                        </div>
                        <div
                            v-if="logoPreview || settings.logo_path"
                            class="mt-1 flex-shrink-0"
                        >
                            <img
                                :src="
                                    logoPreview ||
                                    `/storage/${settings.logo_path}`
                                "
                                alt="Logo preview"
                                class="h-24 w-24 rounded border object-contain p-1"
                            />
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="primary_color">Color primario</Label>
                        <Input
                            id="primary_color"
                            type="color"
                            class="mt-1 block h-10 w-full"
                            name="primary_color"
                            :default-value="settings.primary_color || '#000000'"
                        />
                        <InputError
                            class="mt-2"
                            :message="errors.primary_color"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="secondary_color">Color secundario</Label>
                        <Input
                            id="secondary_color"
                            type="color"
                            class="mt-1 block h-10 w-full"
                            name="secondary_color"
                            :default-value="
                                settings.secondary_color || '#000000'
                            "
                        />
                        <InputError
                            class="mt-2"
                            :message="errors.secondary_color"
                        />
                    </div>
                </div>
            </div>

            <!-- Dirección -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold">Dirección</h2>

                <div class="grid gap-2">
                    <Label for="address.street">Calle</Label>
                    <Input
                        id="address.street"
                        type="text"
                        class="mt-1 block w-full"
                        name="address[street]"
                        :default-value="settings.address?.street || ''"
                        placeholder="Ej. Avenida Paseo de la Reforma"
                    />
                    <InputError
                        class="mt-2"
                        :message="errors['address.street']"
                    />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="address.number">Número exterior</Label>
                        <Input
                            id="address.number"
                            type="text"
                            class="mt-1 block w-full"
                            name="address[number]"
                            :default-value="settings.address?.number || ''"
                            placeholder="Ej. 505"
                        />
                        <InputError
                            class="mt-2"
                            :message="errors['address.number']"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="address.interior_number"
                            >Número interior</Label
                        >
                        <Input
                            id="address.interior_number"
                            type="text"
                            class="mt-1 block w-full"
                            name="address[interior_number]"
                            :default-value="
                                settings.address?.interior_number || ''
                            "
                            placeholder="Ej. Apt. 301"
                        />
                        <InputError
                            class="mt-2"
                            :message="errors['address.interior_number']"
                        />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="address.neighborhood">Colonia</Label>
                    <Input
                        id="address.neighborhood"
                        type="text"
                        class="mt-1 block w-full"
                        name="address[neighborhood]"
                        :default-value="settings.address?.neighborhood || ''"
                        placeholder="Ej. Cuauhtémoc"
                    />
                    <InputError
                        class="mt-2"
                        :message="errors['address.neighborhood']"
                    />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="address.municipality">Municipio</Label>
                        <Input
                            id="address.municipality"
                            type="text"
                            class="mt-1 block w-full"
                            name="address[municipality]"
                            :default-value="
                                settings.address?.municipality || ''
                            "
                            placeholder="Ej. Benito Juárez"
                        />
                        <InputError
                            class="mt-2"
                            :message="errors['address.municipality']"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="address.state">Estado</Label>
                        <Input
                            id="address.state"
                            type="text"
                            class="mt-1 block w-full"
                            name="address[state]"
                            :default-value="settings.address?.state || ''"
                            placeholder="Ej. Ciudad de México"
                        />
                        <InputError
                            class="mt-2"
                            :message="errors['address.state']"
                        />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="address.postal_code">Código postal</Label>
                    <Input
                        id="address.postal_code"
                        type="text"
                        class="mt-1 block w-full"
                        name="address[postal_code]"
                        :default-value="settings.address?.postal_code || ''"
                        placeholder="Ej. 06500"
                    />
                    <InputError
                        class="mt-2"
                        :message="errors['address.postal_code']"
                    />
                </div>
            </div>

            <!-- Preferencias y responsable -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold">
                    Preferencias y responsable
                </h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="timezone">Zona horaria</Label>
                        <Select
                            :default-value="
                                settings.timezone || 'America/Mexico_City'
                            "
                            name="timezone"
                        >
                            <SelectTrigger id="timezone">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="tz in timezones"
                                    :key="tz"
                                    :value="tz"
                                >
                                    {{ tz }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError class="mt-2" :message="errors.timezone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="default_currency"
                            >Moneda predeterminada</Label
                        >
                        <Select
                            :default-value="settings.default_currency || 'MXN'"
                            name="default_currency"
                        >
                            <SelectTrigger id="default_currency">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="currency in currencies"
                                    :key="currency.code"
                                    :value="currency.code"
                                >
                                    {{ currency.code }} - {{ currency.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError
                            class="mt-2"
                            :message="errors.default_currency"
                        />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="proposal_validity_days">
                        Vigencia de propuestas (días)
                    </Label>
                    <Input
                        id="proposal_validity_days"
                        type="number"
                        class="mt-1 block w-full"
                        name="proposal_validity_days"
                        :default-value="settings.proposal_validity_days || 30"
                        min="1"
                        max="365"
                    />
                    <InputError
                        class="mt-2"
                        :message="errors.proposal_validity_days"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="responsible_name">Nombre del responsable</Label>
                    <Input
                        id="responsible_name"
                        type="text"
                        class="mt-1 block w-full"
                        name="responsible_name"
                        :default-value="settings.responsible_name ?? ''"
                        placeholder="Ej. Juan Pérez"
                    />
                    <InputError
                        class="mt-2"
                        :message="errors.responsible_name"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="responsible_title">Cargo del responsable</Label>
                    <Input
                        id="responsible_title"
                        type="text"
                        class="mt-1 block w-full"
                        name="responsible_title"
                        :default-value="settings.responsible_title ?? ''"
                        placeholder="Ej. Director General"
                    />
                    <InputError
                        class="mt-2"
                        :message="errors.responsible_title"
                    />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button
                    :disabled="processing"
                    data-test="update-company-button"
                >
                    Guardar cambios
                </Button>
            </div>
        </Form>
    </div>
</template>
