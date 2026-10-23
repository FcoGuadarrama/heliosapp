<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { nextTick, ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface SizingResult {
    motorVersion: string;
    consumptionSource: 'history' | 'current_period';
    historyDays: number;
    dailyConsumptionKwh: number;
    requiredKwp: number;
    minimumInverterW: number;
    moduleCount: number;
    selectedKwp: number;
    microinverterCount: number | null;
}

const props = defineProps<{ result: SizingResult | null }>();
defineOptions({ layout: { breadcrumbs: [{ title: 'Dimensionador' }] } });

const form = useForm({
    current_kwh: '',
    period_days: '',
    hsp: '',
    module_w: '',
    modules_per_micro: '',
    history_kwh: [] as string[],
});
const result = ref<SizingResult | null>(props.result);
const errorSummary = ref<HTMLElement | null>(null);
const resultPanel = ref<HTMLElement | null>(null);
const fields = [
    {
        name: 'current_kwh',
        label: 'Consumo del período (kWh)',
        min: 0,
        max: 1000000,
        step: 'any',
        required: true,
        hint: 'Transcribe el consumo de energía del recibo.',
    },
    {
        name: 'period_days',
        label: 'Días del período',
        min: 1,
        max: 366,
        step: '1',
        required: true,
        hint: 'Se usa esta duración para cada período histórico.',
    },
    {
        name: 'hsp',
        label: 'Horas de sol pico (h/día)',
        min: 0.1,
        max: 24,
        step: 'any',
        required: true,
        hint: 'Valor manual de prueba. Aún no está conectado a NASA.',
    },
    {
        name: 'module_w',
        label: 'Potencia de un panel (W)',
        min: 1,
        max: 10000,
        step: 'any',
        required: true,
        hint: 'Elige una potencia de referencia, por ejemplo 550 W. No necesitas marca ni modelo.',
    },
    {
        name: 'modules_per_micro',
        label: 'Módulos por microinversor (opcional)',
        min: 1,
        max: 100,
        step: '1',
        required: false,
        hint: 'Captura la capacidad documentada del equipo para contar unidades.',
    },
] as const;

watch(
    () => form.data(),
    () => {
        result.value = null;
    },
    { deep: true },
);

function errorFor(field: string): string | undefined {
    return Object.entries(form.errors).find(([key]) => key === field)?.[1];
}

function errorTarget(field: string): string {
    return field.replaceAll('.', '-');
}

function format(value: number, decimals = 3): string {
    return new Intl.NumberFormat('es-MX', {
        maximumFractionDigits: decimals,
    }).format(value);
}

function calculate(): void {
    result.value = null;
    form.post('/engineering/interconnection', {
        preserveScroll: true,
        onSuccess: async () => {
            result.value = props.result;
            await nextTick();
            resultPanel.value?.focus();
        },
        onError: async () => {
            await nextTick();
            errorSummary.value?.focus();
        },
    });
}
</script>

<template>
    <Head title="Dimensionador de interconexión" />
    <div class="space-y-6">
        <Heading
            title="Dimensionador de interconexión"
            description="Calcula cuántos paneles necesitas y la potencia del inversor con los datos de tu recibo CFE."
        />
        <div
            class="rounded-lg border border-primary/25 bg-primary/5 p-4 text-sm leading-relaxed"
        >
            <p class="font-semibold">Calculadora independiente del proveedor</p>
            <p>
                Captura tu consumo, las horas de sol pico y la potencia de panel
                que quieres evaluar. Obtendrás cantidades y capacidades para
                buscar equipos con cualquier proveedor.
            </p>
        </div>
        <div class="grid items-start gap-6 lg:grid-cols-2">
            <form class="surface-panel space-y-6" @submit.prevent="calculate">
                <div
                    v-if="Object.keys(form.errors).length"
                    ref="errorSummary"
                    tabindex="-1"
                    role="alert"
                    class="rounded-md border border-destructive p-4 text-sm focus:outline-2 focus:outline-ring"
                >
                    <p class="font-semibold">
                        Revisa los datos antes de calcular.
                    </p>
                    <ul class="mt-2 list-inside list-disc">
                        <li
                            v-for="(message, field) in form.errors"
                            :key="field"
                        >
                            <a
                                class="underline"
                                :href="`#${errorTarget(field)}`"
                                >{{ message }}</a
                            >
                        </li>
                    </ul>
                </div>
                <fieldset class="space-y-4" :disabled="form.processing">
                    <legend class="mb-4 text-lg font-semibold">
                        Datos para calcular
                    </legend>
                    <div
                        v-for="field in fields"
                        :key="field.name"
                        class="grid gap-2"
                    >
                        <Label :for="field.name">{{ field.label }}</Label>
                        <Input
                            :id="field.name"
                            v-model="form[field.name]"
                            type="number"
                            :min="field.min"
                            :max="field.max"
                            :step="field.step"
                            :required="field.required"
                            :aria-invalid="!!form.errors[field.name]"
                            :aria-describedby="`${field.name}-hint ${field.name}-error`"
                            class="min-h-11"
                        />
                        <p
                            :id="`${field.name}-hint`"
                            class="text-sm text-muted-foreground"
                        >
                            {{ field.hint }}
                        </p>
                        <InputError
                            :id="`${field.name}-error`"
                            :message="form.errors[field.name]"
                        />
                    </div>
                </fieldset>
                <fieldset
                    id="history_kwh"
                    class="space-y-3"
                    :disabled="form.processing"
                >
                    <legend class="mb-2 font-semibold">
                        Histórico de consumo (opcional)
                    </legend>
                    <p class="text-sm text-muted-foreground">
                        Agrega cada período una sola vez. Con al menos 330 días
                        se usa el promedio histórico; antes, el período actual.
                        Se asumen períodos de igual duración, según la
                        referencia Syscom.
                    </p>
                    <div
                        v-for="(_, index) in form.history_kwh"
                        :key="index"
                        class="space-y-2"
                    >
                        <Label :for="`history_kwh-${index}`"
                            >Período {{ index + 1 }} (kWh)</Label
                        >
                        <div class="flex gap-2">
                            <Input
                                :id="`history_kwh-${index}`"
                                v-model="form.history_kwh[index]"
                                class="min-h-11"
                                type="number"
                                min="0"
                                max="1000000"
                                step="any"
                                required
                                :aria-invalid="
                                    !!errorFor(`history_kwh.${index}`)
                                "
                                :aria-describedby="`history-error-${index}`"
                            />
                            <Button
                                type="button"
                                variant="outline"
                                class="min-h-11"
                                :aria-label="`Quitar período ${index + 1}`"
                                @click="form.history_kwh.splice(index, 1)"
                                >Quitar</Button
                            >
                        </div>
                        <InputError
                            :id="`history-error-${index}`"
                            :message="errorFor(`history_kwh.${index}`)"
                        />
                    </div>
                    <InputError :message="form.errors.history_kwh" />
                    <Button
                        type="button"
                        variant="outline"
                        class="min-h-11"
                        :disabled="form.history_kwh.length >= 24"
                        @click="form.history_kwh.push('')"
                        >Agregar período</Button
                    >
                </fieldset>
                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="min-h-11 w-full"
                    >{{
                        form.processing
                            ? 'Calculando…'
                            : 'Calcular dimensionamiento'
                    }}</Button
                >
            </form>
            <section
                ref="resultPanel"
                tabindex="-1"
                aria-labelledby="result-heading"
                class="surface-panel space-y-5 focus:outline-2 focus:outline-ring"
            >
                <h2 id="result-heading" class="text-lg font-semibold">
                    Qué necesitas
                </h2>
                <template v-if="result">
                    <ul
                        class="space-y-4 rounded-lg border border-primary/25 bg-primary/5 p-4"
                    >
                        <li>
                            <p class="text-lg font-semibold tabular-nums">
                                {{ result.moduleCount }} paneles de
                                {{
                                    format(
                                        (result.selectedKwp * 1000) /
                                            result.moduleCount,
                                    )
                                }}
                                W
                            </p>
                            <p class="text-sm text-muted-foreground">
                                {{ format(result.selectedKwp) }} kWp de potencia
                                total en paneles.
                            </p>
                        </li>
                        <li>
                            <p class="text-lg font-semibold tabular-nums">
                                1 inversor de al menos
                                {{ format(result.minimumInverterW / 1000) }} kW
                            </p>
                            <p class="text-sm text-muted-foreground">
                                Capacidad AC mínima de referencia. Busca un
                                equipo que admita la potencia total de los
                                paneles y sus características eléctricas.
                            </p>
                        </li>
                        <li v-if="result.microinverterCount !== null">
                            <p class="font-semibold tabular-nums">
                                Alternativa: {{ result.microinverterCount }}
                                {{
                                    result.microinverterCount === 1
                                        ? 'microinversor'
                                        : 'microinversores'
                                }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                En lugar del inversor central, según los módulos
                                por unidad que capturaste. Falta comprobar la
                                capacidad eléctrica de cada equipo.
                            </p>
                        </li>
                    </ul>
                    <p class="text-sm text-muted-foreground">
                        Dimensionamiento preliminar de interconexión CFE para
                        cubrir el 100% del consumo calculado. Marcas, precios,
                        disponibilidad, estructura, cableado y protecciones se
                        definen al diseñar la instalación. No se incluyen
                        baterías en este cálculo.
                    </p>
                    <h3 class="font-semibold">Detalle del cálculo</h3>
                    <dl class="divide-y divide-border text-sm">
                        <div class="flex justify-between gap-4 py-3">
                            <dt>Consumo diario</dt>
                            <dd class="font-semibold tabular-nums">
                                {{ format(result.dailyConsumptionKwh) }} kWh/día
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4 py-3">
                            <dt>Potencia DC requerida</dt>
                            <dd class="font-semibold tabular-nums">
                                {{ format(result.requiredKwp) }} kWp
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4 py-3">
                            <dt>Paneles necesarios</dt>
                            <dd class="font-semibold tabular-nums">
                                {{ result.moduleCount }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4 py-3">
                            <dt>Potencia DC seleccionada</dt>
                            <dd class="font-semibold tabular-nums">
                                {{ format(result.selectedKwp) }} kWp
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4 py-3">
                            <dt>Capacidad AC mínima del inversor</dt>
                            <dd class="font-semibold tabular-nums">
                                {{ format(result.minimumInverterW, 0) }} W
                            </dd>
                        </div>
                        <div
                            v-if="result.microinverterCount !== null"
                            class="flex justify-between gap-4 py-3"
                        >
                            <dt>Microinversores por módulos admitidos</dt>
                            <dd class="font-semibold tabular-nums">
                                {{ result.microinverterCount }}
                            </dd>
                        </div>
                    </dl>
                    <p class="text-sm text-muted-foreground">
                        Base:
                        {{
                            result.consumptionSource === 'history'
                                ? 'promedio histórico'
                                : 'período actual'
                        }}. Histórico considerado:
                        {{ format(result.historyDays, 0) }} días. Si supera 400
                        días, se normaliza a 365.
                    </p>
                    <p class="text-sm text-muted-foreground">
                        El mínimo del inversor usa la potencia requerida antes
                        de redondear paneles. El conteo de microinversores no
                        valida potencia, tensión, corriente ni compatibilidad
                        del equipo.
                    </p>
                    <p class="text-sm text-muted-foreground">
                        Las HSP son manuales; aún no se consultan datos NASA. El
                        resultado no verifica compatibilidad eléctrica ni
                        condiciones del techo, no incluye precios y no se
                        guarda.
                    </p>
                    <p class="text-sm text-muted-foreground">
                        Cubrir el consumo calculado no garantiza eliminar el
                        importe del recibo. La instalación no está incluida. Se
                        cotiza por separado.
                    </p>
                    <p class="text-xs text-muted-foreground">
                        Motor: {{ result.motorVersion }}
                    </p>
                </template>
                <p v-else class="text-sm leading-relaxed text-muted-foreground">
                    Completa el consumo, los días, las horas de sol pico y la
                    potencia de un panel. Aquí verás cuántos paneles necesitas,
                    su potencia total y la capacidad mínima del inversor. No
                    necesitas una cuenta de proveedor ni consultar su catálogo.
                </p>
            </section>
        </div>
    </div>
</template>
