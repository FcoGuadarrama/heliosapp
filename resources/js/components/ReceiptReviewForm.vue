<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { review } from '@/routes/intakes';
import type {
    ReceiptAnalysisData,
    ReceiptReviewForm,
    StateOption,
} from '@/types/receipt-analysis';

const props = defineProps<{
    intakeId: number;
    analysis: ReceiptAnalysisData;
    states: StateOption[];
}>();
const data = props.analysis.form;
const form = useForm<ReceiptReviewForm>({
    tariff_code: data?.tariff_code ?? '',
    consumption_kwh: data?.consumption_kwh ?? null,
    period_start: data?.period_start ?? '',
    period_end: data?.period_end ?? '',
    phases: data?.phases ?? null,
    days: data?.days ?? null,
    state_code: data?.state_code ?? '',
    history: (data?.history ?? []).map((period) => ({ ...period })),
    concepts: (data?.concepts ?? []).map((concept) => ({ ...concept })),
    period_amount_mxn: data?.period_amount_mxn ?? null,
    total_amount_mxn: data?.total_amount_mxn ?? null,
    revision: props.analysis.revision,
    reason: '',
    confirmed: false,
});
const summary = ref<HTMLElement | null>(null);
const errors = computed(() => Object.entries(form.errors));
const id = (field: string) =>
    `receipt-${props.intakeId}-${field.replaceAll('.', '-')}`;
const inputClass = 'h-11 w-full rounded-md border bg-background px-3 text-base';
function submit() {
    form.post(review(props.intakeId).url, {
        preserveScroll: true,
        onError: () => nextTick(() => summary.value?.focus()),
    });
}
</script>

<template>
    <form class="space-y-5" @submit.prevent="submit">
        <div
            v-if="errors.length"
            ref="summary"
            tabindex="-1"
            role="alert"
            class="rounded-md border border-destructive p-4"
        >
            <p class="font-medium">Revisa los datos antes de continuar</p>
            <ul class="mt-2 list-inside list-disc text-sm">
                <li v-for="[field, message] in errors" :key="field">
                    <a :href="`#${id(field)}`" class="underline">{{
                        message
                    }}</a>
                </li>
            </ul>
        </div>
        <p class="text-sm text-muted-foreground">
            Compara las cifras con el recibo original. Los datos corregidos
            quedan registrados con tu confirmación.
        </p>
        <div class="grid gap-4 sm:grid-cols-2">
            <label
                :for="id('tariff_code')"
                class="space-y-1 text-sm font-medium"
                >Tarifa
                <input
                    :id="id('tariff_code')"
                    v-model="form.tariff_code"
                    :class="inputClass"
                    required
                    maxlength="16"
                />
            </label>
            <label
                :for="id('consumption_kwh')"
                class="space-y-1 text-sm font-medium"
                >Consumo actual (kWh)
                <input
                    :id="id('consumption_kwh')"
                    v-model.number="form.consumption_kwh"
                    :class="inputClass"
                    type="number"
                    step="any"
                    min="0"
                    required
                />
            </label>
            <label
                :for="id('period_start')"
                class="space-y-1 text-sm font-medium"
                >Inicio del periodo
                <input
                    :id="id('period_start')"
                    v-model="form.period_start"
                    :class="inputClass"
                    type="date"
                    required
                />
            </label>
            <label :for="id('period_end')" class="space-y-1 text-sm font-medium"
                >Fin del periodo
                <input
                    :id="id('period_end')"
                    v-model="form.period_end"
                    :class="inputClass"
                    type="date"
                    required
                />
            </label>
            <label :for="id('state_code')" class="space-y-1 text-sm font-medium"
                >Estado de la instalación
                <select
                    :id="id('state_code')"
                    v-model="form.state_code"
                    :class="inputClass"
                    required
                >
                    <option value="">
                        Selecciona el estado del suministro
                    </option>
                    <option
                        v-for="state in states"
                        :key="state.code"
                        :value="state.code"
                    >
                        {{ state.name }}
                    </option>
                </select>
            </label>
            <label :for="id('phases')" class="space-y-1 text-sm font-medium"
                >Fases, solo si están impresas
                <select
                    :id="id('phases')"
                    v-model="form.phases"
                    :class="inputClass"
                >
                    <option :value="null">No impresas</option>
                    <option
                        v-for="phase in [1, 2, 3]"
                        :key="phase"
                        :value="phase"
                    >
                        {{ phase }}
                    </option>
                </select>
                <span class="block font-normal text-muted-foreground"
                    >“NO HILOS” no indica las fases.</span
                >
            </label>
            <label :for="id('days')" class="space-y-1 text-sm font-medium"
                >Días impresos, si aparecen
                <input
                    :id="id('days')"
                    v-model.number="form.days"
                    :class="inputClass"
                    type="number"
                    min="1"
                    max="75"
                />
                <span class="block font-normal text-muted-foreground"
                    >Déjalo vacío si no aparecen. La duración se obtiene de las
                    fechas.</span
                >
            </label>
        </div>
        <fieldset class="space-y-3">
            <legend class="font-medium">Histórico del recibo</legend>
            <div
                v-for="(period, index) in form.history"
                :key="index"
                class="grid gap-3 rounded-md border p-3 sm:grid-cols-2 lg:grid-cols-4"
            >
                <label
                    :for="id(`history.${index}.period_start`)"
                    class="space-y-1 text-sm"
                    >Inicio {{ index + 1 }}
                    <input
                        :id="id(`history.${index}.period_start`)"
                        v-model="period.period_start"
                        :class="inputClass"
                        type="date"
                    />
                </label>
                <label
                    :for="id(`history.${index}.period_end`)"
                    class="space-y-1 text-sm"
                    >Fin {{ index + 1 }}
                    <input
                        :id="id(`history.${index}.period_end`)"
                        v-model="period.period_end"
                        :class="inputClass"
                        type="date"
                    />
                </label>
                <label
                    :for="id(`history.${index}.consumption_kwh`)"
                    class="space-y-1 text-sm"
                    >Consumo (kWh)
                    <input
                        :id="id(`history.${index}.consumption_kwh`)"
                        v-model.number="period.consumption_kwh"
                        :class="inputClass"
                        type="number"
                        step="any"
                        min="0"
                    />
                </label>
                <Button
                    type="button"
                    variant="outline"
                    class="min-h-11 self-end"
                    :aria-label="`Quitar periodo ${index + 1}`"
                    @click="form.history.splice(index, 1)"
                    >Quitar periodo</Button
                >
            </div>
            <Button
                type="button"
                variant="outline"
                class="min-h-11"
                @click="
                    form.history.push({
                        period_start: null,
                        period_end: null,
                        consumption_kwh: null,
                        days: null,
                        amount_mxn: null,
                    })
                "
                >Agregar periodo impreso</Button
            >
        </fieldset>
        <details class="rounded-md border p-4">
            <summary class="min-h-11 cursor-pointer py-2 text-sm font-medium">
                Revisar importes leídos del recibo
            </summary>
            <div class="mt-3 grid gap-4 sm:grid-cols-2">
                <label :for="id('period_amount_mxn')" class="space-y-1 text-sm"
                    >Importe del periodo (MXN)<input
                        :id="id('period_amount_mxn')"
                        v-model.number="form.period_amount_mxn"
                        :class="inputClass"
                        type="number"
                        min="0"
                        step="0.01"
                /></label>
                <label :for="id('total_amount_mxn')" class="space-y-1 text-sm"
                    >Total a pagar (MXN)<input
                        :id="id('total_amount_mxn')"
                        v-model.number="form.total_amount_mxn"
                        :class="inputClass"
                        type="number"
                        min="0"
                        step="0.01"
                /></label>
            </div>
            <div
                v-for="(concept, index) in form.concepts"
                :key="index"
                class="mt-3 grid gap-3 sm:grid-cols-3"
            >
                <label
                    :for="id(`concepts.${index}.description`)"
                    class="space-y-1 text-sm"
                    >Concepto del periodo<input
                        :id="id(`concepts.${index}.description`)"
                        v-model="concept.description"
                        :class="inputClass"
                /></label>
                <label
                    :for="id(`concepts.${index}.amount_mxn`)"
                    class="space-y-1 text-sm"
                    >Importe (MXN)<input
                        :id="id(`concepts.${index}.amount_mxn`)"
                        v-model.number="concept.amount_mxn"
                        :class="inputClass"
                        type="number"
                        min="0"
                        step="0.01"
                /></label>
                <Button
                    type="button"
                    variant="outline"
                    class="min-h-11 self-end"
                    @click="form.concepts.splice(index, 1)"
                    >Quitar concepto</Button
                >
            </div>
        </details>
        <label :for="id('reason')" class="block space-y-1 text-sm font-medium"
            >Nota de revisión
            <textarea
                :id="id('reason')"
                v-model="form.reason"
                class="min-h-24 w-full rounded-md border bg-background p-3 text-base"
                required
                minlength="10"
                maxlength="1000"
                placeholder="Indica qué cotejaste o corregiste en el recibo."
            ></textarea>
        </label>
        <label
            :for="id('confirmed')"
            class="flex min-h-11 items-center gap-3 text-sm"
        >
            <input
                :id="id('confirmed')"
                v-model="form.confirmed"
                type="checkbox"
                required
                class="size-5"
            />
            Verifiqué los datos y periodos contra el recibo original.
        </label>
        <Button type="submit" class="min-h-11" :disabled="form.processing">{{
            form.processing
                ? 'Guardando confirmación…'
                : 'Confirmar datos y continuar'
        }}</Button>
    </form>
</template>
