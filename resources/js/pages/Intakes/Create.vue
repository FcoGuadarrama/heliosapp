<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import IntakeController from '@/actions/App/Http/Controllers/IntakeController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import ReceiptFileField from '@/components/ReceiptFileField.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as clientsIndex } from '@/routes/clients';

const props = defineProps<{
    clients: Array<{ id: number; name: string }>;
    privacyNotice: { version: string; text: string; url: string };
    intakeEnabled: boolean;
    selectedClientId?: number | null;
    maxFileKb?: number;
    embed: { url: string; snippet: string; frameAncestors?: string };
}>();
const page = usePage();
const canCreateClient = computed(() =>
    page.props.auth.permissions.includes('clients.create'),
);
const mode = ref(
    props.selectedClientId || !canCreateClient.value ? 'existing' : 'new',
);
const copyStatus = ref('');
const snippetField = ref<HTMLTextAreaElement | null>(null);
async function copyEmbed(): Promise<void> {
    try {
        await navigator.clipboard.writeText(props.embed.snippet);
        copyStatus.value = 'Código copiado.';
    } catch {
        snippetField.value?.focus();
        snippetField.value?.select();
        copyStatus.value = 'Selecciona y copia el código con el teclado.';
    }
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Mis prospectos', href: clientsIndex() },
            { title: 'Recibir CFE' },
        ],
    },
});
</script>

<template>
    <Head title="Recibir CFE" />
    <div class="max-w-2xl space-y-6">
        <Heading
            variant="small"
            title="Recibir CFE"
            description="Guarda un recibo con un prospecto nuevo o existente. No se requiere proyecto."
        />
        <p
            v-if="!intakeEnabled"
            role="status"
            class="rounded-md border p-4 text-sm"
        >
            La recepción de archivos no está configurada. Solicita a un
            administrador que active el formulario y configure el aviso de
            privacidad.
        </p>
        <Form
            v-bind="IntakeController.store.form()"
            v-slot="{ errors, processing, progress }"
            class="space-y-6"
        >
            <fieldset
                :disabled="processing || !intakeEnabled"
                class="space-y-6"
            >
                <div class="space-y-3 rounded-lg border p-6">
                    <h2 class="mb-3 font-semibold">Prospecto</h2>
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center gap-2"
                            ><input
                                v-model="mode"
                                type="radio"
                                value="new"
                                :disabled="!canCreateClient"
                                name="prospect_mode"
                            />Nuevo prospecto</label
                        >
                        <label class="flex items-center gap-2"
                            ><input
                                v-model="mode"
                                type="radio"
                                value="existing"
                                name="prospect_mode"
                                :disabled="clients.length === 0"
                            />Prospecto existente</label
                        >
                    </div>
                    <div v-if="mode === 'existing'" class="grid gap-2">
                        <Label for="client_id">Selecciona un prospecto</Label>
                        <select
                            id="client_id"
                            name="client_id"
                            :value="selectedClientId ?? ''"
                            required
                            class="h-10 w-full rounded-md border bg-background px-3 text-sm"
                            :aria-invalid="!!errors.client_id"
                        >
                            <option value="" disabled>
                                Selecciona un prospecto
                            </option>
                            <option
                                v-for="client in clients"
                                :key="client.id"
                                :value="client.id"
                            >
                                {{ client.name }}
                            </option>
                        </select>
                        <InputError :message="errors.client_id" />
                    </div>
                    <div v-else class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="name">Nombre</Label
                            ><Input
                                id="name"
                                name="name"
                                required
                                maxlength="255"
                                :aria-invalid="!!errors.name"
                            /><InputError :message="errors.name" />
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Indica al menos un medio de contacto: correo o
                            teléfono.
                        </p>
                        <div class="grid gap-2">
                            <Label for="email">Correo</Label
                            ><Input
                                id="email"
                                name="email"
                                type="email"
                                maxlength="255"
                                :aria-invalid="!!errors.email"
                            /><InputError :message="errors.email" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="phone">Teléfono</Label
                            ><Input
                                id="phone"
                                name="phone"
                                type="tel"
                                maxlength="20"
                                :aria-invalid="!!errors.phone"
                            /><InputError :message="errors.phone" />
                        </div>
                    </div>
                </div>
                <ReceiptFileField
                    name="receipt"
                    :error="errors.receipt"
                    :max-size-mb="(maxFileKb ?? 10240) / 1024"
                />
                <div class="space-y-3 rounded-lg border p-4">
                    <p class="text-sm whitespace-pre-line">
                        {{ privacyNotice.text }}
                    </p>
                    <a
                        v-if="privacyNotice.url"
                        :href="privacyNotice.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-sm underline"
                        >Consultar aviso de privacidad</a
                    >
                    <input
                        type="hidden"
                        name="privacy_version"
                        :value="privacyNotice.version"
                    />
                    <label
                        for="privacy_consent"
                        class="flex items-start gap-3 text-sm"
                        ><input
                            id="privacy_consent"
                            type="checkbox"
                            name="privacy_consent"
                            value="1"
                            required
                            class="mt-0.5 size-4 shrink-0"
                        />Confirmo que el prospecto autorizó el tratamiento de
                        sus datos y de su recibo conforme a este aviso de
                        privacidad.</label
                    >
                    <InputError :message="errors.privacy_consent" /><InputError
                        :message="errors.privacy_version"
                    />
                </div>
                <p v-if="progress" role="status" class="text-sm">
                    Subiendo archivo: {{ progress.percentage }}%
                </p>
                <Button
                    type="submit"
                    :disabled="processing || !intakeEnabled"
                    >{{
                        processing
                            ? 'Guardando recibo…'
                            : 'Guardar prospecto y recibo'
                    }}</Button
                >
            </fieldset>
        </Form>
        <section
            class="space-y-3 rounded-lg border p-4"
            aria-labelledby="embed-heading"
        >
            <h2 id="embed-heading" class="font-medium">
                Formulario público para tu sitio
            </h2>
            <p class="my-3 text-sm text-muted-foreground">
                Comparte el formulario o intégralo en un sitio autorizado por el
                administrador. La lista de dominios permitidos se controla en el
                servidor.
            </p>
            <a
                :href="embed.url"
                target="_blank"
                rel="noopener noreferrer"
                class="text-sm underline"
                >Abrir formulario público</a
            >
            <div class="my-4 space-y-2">
                <h2 class="text-sm font-medium">
                    Dominios permitidos para incrustar
                </h2>
                <p
                    v-if="embed.frameAncestors === '\'none\''"
                    class="text-sm text-muted-foreground"
                >
                    Ninguno. El administrador debe autorizar el dominio de tu
                    sitio antes de incrustar el formulario.
                </p>
                <p
                    v-else-if="embed.frameAncestors"
                    class="rounded bg-muted p-3 text-sm break-all"
                >
                    {{ embed.frameAncestors }}
                </p>
                <p v-else class="text-sm text-muted-foreground">
                    Consulta la lista efectiva de dominios en la configuración
                    del servidor.
                </p>
                <p class="text-sm text-muted-foreground">
                    Se admiten orígenes HTTPS exactos, sin rutas ni comodines.
                    La URL pública también puede abrirse directamente.
                </p>
            </div>
            <label for="embed-snippet" class="mt-4 block text-sm font-medium"
                >Código iframe</label
            >
            <textarea
                id="embed-snippet"
                ref="snippetField"
                :value="embed.snippet"
                readonly
                rows="4"
                class="my-2 w-full rounded-md border bg-muted p-3 font-mono text-xs"
            />
            <Button type="button" variant="outline" @click="copyEmbed"
                >Copiar código iframe</Button
            >
            <p role="status" class="mt-2 text-sm">{{ copyStatus }}</p>
        </section>
        <Link :href="clientsIndex()" class="text-sm underline"
            >Volver a Mis prospectos</Link
        >
    </div>
</template>
