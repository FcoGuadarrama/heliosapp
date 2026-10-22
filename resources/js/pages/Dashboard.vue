<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { index as clientsIndex } from '@/routes/clients';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Dashboard', href: dashboard() }] },
});
</script>

<template>
    <Head title="Dashboard" />
    <div class="space-y-6">
        <Heading
            title="CRM de cotizaciones solares"
            description="Recibe prospectos y reúne sus recibos CFE en un solo lugar."
        />
        <section class="max-w-2xl space-y-4 rounded-lg border p-6">
            <h2 class="text-lg font-semibold">
                Empieza con un prospecto y su recibo
            </h2>
            <p class="text-sm text-muted-foreground">
                Crea o selecciona un prospecto y adjunta un PDF o una imagen de
                su recibo CFE. También puedes recibir solicitudes desde el
                formulario público de tu sitio. No necesitas crear un proyecto.
            </p>
            <Button as-child
                ><Link :href="clientsIndex()">Ir a Mis prospectos</Link></Button
            >
        </section>
        <section class="max-w-2xl space-y-2">
            <h2 class="font-semibold">Revisión antes de cotizar</h2>
            <p class="text-sm text-muted-foreground">
                La carga conserva el archivo para su revisión. La extracción
                automática con Ollama está pendiente; los datos de prueba se
                identificarán expresamente. El dimensionamiento requiere
                información energética y coordenadas verificadas.
            </p>
        </section>
    </div>
</template>
