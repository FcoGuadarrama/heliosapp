<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import ReceiptReviewForm from '@/components/ReceiptReviewForm.vue';
import EquipmentQuotePanel from '@/components/EquipmentQuotePanel.vue';
import { analyze, replacement } from '@/routes/intakes';
import type {
    ReceiptAnalysisData,
    StateOption,
} from '@/types/receipt-analysis';

const props = defineProps<{
    intakeId: number;
    status: string;
    analysis: ReceiptAnalysisData;
    states: StateOption[];
}>();
const retry = useForm({});
const upload = useForm<{ receipt: File | null }>({ receipt: null });
const busy = computed(() =>
    ['queued', 'running'].includes(props.analysis.processing),
);
const messages: Record<string, string> = {
    receipt_reader_unavailable:
        'El lector no está disponible. Puedes reintentar cuando la PC de análisis esté conectada.',
    receipt_reading_failed:
        'El lector no pudo completar la extracción. El archivo sigue guardado; puedes reintentar.',
    receipt_needs_review:
        'Hay datos que requieren cotejo con el recibo original.',
    receipt_unreadable:
        'No se pudo leer el recibo. Se necesita otra copia o revisión del asesor.',
    supply_state_required:
        'Confirma el estado de la instalación para usar la radiación solar correspondiente.',
    hsp_unavailable:
        'No hay datos solares vigentes disponibles para este estado.',
    queue_dispatch_failed:
        'El archivo está guardado. No se pudo iniciar el análisis; puedes reintentar.',
    queue_configuration_invalid:
        'El servicio de procesamiento necesita configuración. El archivo está guardado.',
    processing_failed:
        'El análisis se interrumpió. El archivo está guardado y puede reintentarse.',
};
const warnings = computed(() => [
    ...props.analysis.validation.errors,
    ...props.analysis.validation.warnings,
    ...props.analysis.validation.extraction_warnings,
]);
function pickFile(event: Event) {
    if (event.target instanceof HTMLInputElement)
        upload.receipt = event.target.files?.[0] ?? null;
}
</script>

<template>
    <div class="space-y-4">
        <div
            role="status"
            aria-live="polite"
            class="rounded-md border bg-muted/30 p-4 text-sm"
        >
            <p v-if="analysis.processing === 'queued'" class="font-medium">
                Recibo en cola de análisis
            </p>
            <p
                v-else-if="analysis.processing === 'running'"
                class="font-medium"
            >
                Leyendo y comprobando el recibo…
            </p>
            <p v-else-if="analysis.result" class="font-medium">
                Dimensionamiento preliminar disponible
            </p>
            <p v-else-if="analysis.reason">
                {{
                    messages[analysis.reason] ??
                    'El análisis requiere revisión del asesor.'
                }}
            </p>
            <p v-else>Archivo recibido. Puedes solicitar su análisis.</p>
            <p v-if="busy" class="mt-1 text-muted-foreground">
                El análisis continúa en segundo plano. Esta pantalla se
                actualiza automáticamente.
            </p>
        </div>
        <div v-if="analysis.result" class="space-y-3 rounded-md border p-4">
            <dl class="grid gap-4 sm:grid-cols-3">
                <div>
                    <dt class="text-sm text-muted-foreground">
                        Potencia requerida
                    </dt>
                    <dd class="text-xl font-semibold">
                        {{ analysis.result.requiredKwp.toFixed(2) }} kWp
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">
                        Inversor mínimo de referencia
                    </dt>
                    <dd class="text-xl font-semibold">
                        {{
                            (analysis.result.minimumInverterW / 1000).toFixed(2)
                        }}
                        kW
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">
                        Consumo diario utilizado
                    </dt>
                    <dd class="text-xl font-semibold">
                        {{ analysis.result.dailyConsumptionKwh.toFixed(2) }} kWh
                    </dd>
                </div>
            </dl>
            <p class="text-sm text-muted-foreground">
                Cantidad de paneles pendiente de configurar la potencia del
                módulo. Este resultado no selecciona equipos ni incluye precio
                comercial.
            </p>
            <p v-if="analysis.hsp" class="text-sm text-muted-foreground">
                HSP estatal NASA: {{ analysis.hsp.annual.toFixed(2) }} h/día ·
                {{ analysis.hsp.stateName }}.
            </p>
        </div>
        <ul
            v-if="warnings.length"
            class="space-y-1 text-sm text-muted-foreground"
            aria-label="Observaciones de la lectura"
        >
            <li v-for="(warning, index) in warnings" :key="index">
                {{ warning.message }}
            </li>
        </ul>
        <EquipmentQuotePanel
            v-if="
                analysis.result &&
                analysis.calculation_id &&
                analysis.can_process &&
                !busy
            "
            :key="analysis.calculation_id"
            :intake-id="intakeId"
            :calculation-id="analysis.calculation_id"
            :draft="analysis.quote_draft"
        />
        <details
            v-if="analysis.has_reading && analysis.can_process && !busy"
            class="rounded-md border p-4"
            :open="!analysis.result"
        >
            <summary class="min-h-11 cursor-pointer py-2 font-medium">
                Revisar y confirmar datos del recibo
            </summary>
            <ReceiptReviewForm
                class="mt-4"
                :intake-id="intakeId"
                :analysis="analysis"
                :states="states"
            />
        </details>
        <Button
            v-if="analysis.can_process && !busy && !analysis.result"
            variant="outline"
            class="min-h-11"
            :disabled="retry.processing"
            @click="retry.post(analyze(intakeId).url, { preserveScroll: true })"
            >{{
                retry.processing ? 'Solicitando…' : 'Reintentar análisis'
            }}</Button
        >
        <form
            v-if="analysis.can_replace && !busy"
            class="space-y-3 rounded-md border p-4"
            @submit.prevent="
                upload.post(replacement(intakeId).url, { preserveScroll: true })
            "
        >
            <label
                :for="`replacement-${intakeId}`"
                class="block text-sm font-medium"
                >Segunda copia del recibo</label
            >
            <input
                :id="`replacement-${intakeId}`"
                type="file"
                accept="application/pdf,image/jpeg,image/png,image/webp"
                class="min-h-11 w-full text-base"
                required
                @change="pickFile"
            />
            <p
                v-if="upload.errors.receipt"
                class="text-sm text-destructive"
                role="alert"
            >
                {{ upload.errors.receipt }}
            </p>
            <Button type="submit" class="min-h-11" :disabled="upload.processing"
                >Enviar segunda copia</Button
            >
        </form>
        <p
            v-if="status === 'unreadable' && analysis.upload_count >= 2"
            class="text-sm text-muted-foreground"
        >
            Ya se recibieron dos copias. El expediente requiere revisión del
            asesor; no se solicitará una tercera carga.
        </p>
    </div>
</template>
