<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { EquipmentQuoteDraft } from '@/types/receipt-analysis';

interface Product {
    id: string;
    model: string;
    title: string;
    list_price: string | null;
    stock: number | null;
}
const props = defineProps<{
    intakeId: number;
    calculationId: number;
    draft: EquipmentQuoteDraft | null;
}>();
const query = ref('');
const products = ref<Product[]>([]);
const searching = ref(false);
const searchError = ref('');
const searched = ref(false);
const page = ref(1);
const pages = ref(1);
const errorSummary = ref<HTMLElement | null>(null);
const form = useForm({
    calculation_id: props.calculationId,
    submission_id: crypto.randomUUID(),
    confirmed: false,
    lines: [] as { product_id: string; quantity: number; model: string }[],
});
const money = (cents: number) =>
    new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(cents / 100);

function errorFor(field: string): string | undefined {
    return Object.entries(form.errors).find(([key]) => key === field)?.[1];
}
function errorTarget(field: string): string {
    const quantity = /^lines\.(\d+)\.quantity$/.exec(field);
    if (quantity) return `quote-quantity-${props.intakeId}-${quantity[1]}`;
    return field === 'confirmed'
        ? `quote-confirmed-${props.intakeId}`
        : `quote-form-${props.intakeId}`;
}

async function search(nextPage = 1) {
    if (searching.value) return;
    searching.value = true;
    searchError.value = '';
    try {
        const parameters = new URLSearchParams({
            query: query.value,
            page: String(nextPage),
        });
        const response = await fetch(
            `/intakes/${props.intakeId}/catalog?${parameters}`,
            {
                headers: { Accept: 'application/json' },
            },
        );
        const data = await response.json();
        if (!response.ok)
            throw new Error(
                data.message ?? 'No se pudo consultar el catálogo.',
            );
        products.value = data.products;
        page.value = data.page;
        pages.value = data.pages;
        searched.value = true;
    } catch (error) {
        searchError.value =
            error instanceof Error
                ? error.message
                : 'No se pudo consultar el catálogo.';
    } finally {
        searching.value = false;
    }
}
function add(product: Product) {
    if (
        form.lines.some((line) => line.product_id === product.id) ||
        form.lines.length >= 20
    )
        return;
    form.lines.push({
        product_id: product.id,
        quantity: 1,
        model: product.model,
    });
    form.confirmed = false;
}
function save() {
    form.post(`/intakes/${props.intakeId}/quote-drafts`, {
        preserveScroll: true,
        onSuccess: () => {
            form.submission_id = crypto.randomUUID();
            form.confirmed = false;
        },
        onError: async () => {
            await nextTick();
            errorSummary.value?.focus();
        },
    });
}
</script>

<template>
    <section
        class="space-y-4 rounded-md border p-4"
        :aria-labelledby="`quote-title-${intakeId}`"
    >
        <h3 :id="`quote-title-${intakeId}`" class="font-semibold">
            Cotizador asistido · borrador interno
        </h3>
        <p class="text-sm text-muted-foreground">
            Selecciona los equipos y cantidades. Se consultará el precio de
            lista de Syscom en MXN sin IVA al guardar. La compatibilidad
            eléctrica queda pendiente de validar con las fichas técnicas.
        </p>
        <form class="space-y-2" @submit.prevent="search()">
            <label
                :for="`catalog-query-${intakeId}`"
                class="text-sm font-medium"
                >Buscar equipo en Syscom</label
            >
            <div class="flex flex-wrap gap-2">
                <Input
                    :id="`catalog-query-${intakeId}`"
                    v-model="query"
                    class="min-h-11 min-w-0 flex-1 text-base"
                    required
                    minlength="2"
                    maxlength="120"
                    placeholder="Panel solar, inversor o modelo"
                />
                <Button class="min-h-11" type="submit" :disabled="searching">{{
                    searching ? 'Consultando…' : 'Buscar'
                }}</Button>
            </div>
            <p v-if="searchError" role="alert" class="text-sm text-destructive">
                {{ searchError }}
            </p>
        </form>
        <div aria-live="polite" class="space-y-2">
            <p v-if="searched && !products.length" class="text-sm">
                No se encontraron equipos.
            </p>
            <div
                v-for="product in products"
                :key="product.id"
                class="flex flex-wrap items-center justify-between gap-3 rounded border p-3"
            >
                <div class="min-w-0 flex-1 break-words">
                    <p class="font-medium">{{ product.model }}</p>
                    <p class="text-sm">{{ product.title }}</p>
                    <p class="text-sm text-muted-foreground">
                        Precio de lista:
                        {{
                            product.list_price !== null
                                ? money(
                                      Math.round(
                                          Number(product.list_price) * 100,
                                      ),
                                  )
                                : 'No disponible'
                        }}
                        · Existencia: {{ product.stock ?? 'Sin informar' }}
                    </p>
                </div>
                <Button
                    type="button"
                    variant="outline"
                    class="min-h-11"
                    :disabled="
                        form.processing ||
                        !product.list_price ||
                        Number(product.list_price) <= 0 ||
                        form.lines.length >= 20 ||
                        form.lines.some(
                            (line) => line.product_id === product.id,
                        )
                    "
                    :aria-label="`Agregar ${product.model}`"
                    @click="add(product)"
                    >Agregar</Button
                >
            </div>
            <div v-if="pages > 1" class="flex flex-wrap items-center gap-3">
                <Button
                    type="button"
                    variant="outline"
                    class="min-h-11"
                    :disabled="searching || page <= 1"
                    @click="search(page - 1)"
                    >Anterior</Button
                >
                <span class="text-sm">Página {{ page }} de {{ pages }}</span>
                <Button
                    type="button"
                    variant="outline"
                    class="min-h-11"
                    :disabled="searching || page >= pages"
                    @click="search(page + 1)"
                    >Siguiente</Button
                >
            </div>
        </div>
        <form
            v-if="form.lines.length"
            :id="`quote-form-${intakeId}`"
            tabindex="-1"
            class="space-y-4 border-t pt-4"
            @submit.prevent="save"
        >
            <div
                v-if="Object.keys(form.errors).length"
                ref="errorSummary"
                role="alert"
                tabindex="-1"
                class="rounded border border-destructive p-3 text-sm text-destructive"
            >
                <p class="font-medium">Revisa el borrador</p>
                <p v-for="(message, field) in form.errors" :key="field">
                    <a
                        class="underline"
                        :href="`#${errorTarget(String(field))}`"
                        >{{ message }}</a
                    >
                </p>
            </div>
            <div
                v-for="(line, index) in form.lines"
                :key="line.product_id"
                class="flex flex-wrap items-end gap-3"
            >
                <div class="min-w-0 flex-1 space-y-1">
                    <label
                        :for="`quote-quantity-${intakeId}-${index}`"
                        class="block text-sm font-medium break-words"
                        >Cantidad · {{ line.model }}</label
                    >
                    <Input
                        :id="`quote-quantity-${intakeId}-${index}`"
                        v-model.number="line.quantity"
                        class="min-h-11 text-base"
                        type="number"
                        min="1"
                        max="1000"
                        step="1"
                        required
                        :disabled="form.processing"
                        :aria-invalid="
                            Boolean(errorFor(`lines.${index}.quantity`))
                        "
                        :aria-describedby="`quote-quantity-error-${intakeId}-${index}`"
                        @input="form.confirmed = false"
                    />
                    <p
                        :id="`quote-quantity-error-${intakeId}-${index}`"
                        class="text-sm text-destructive"
                    >
                        {{ errorFor(`lines.${index}.quantity`) }}
                    </p>
                </div>
                <Button
                    type="button"
                    variant="outline"
                    class="min-h-11"
                    :disabled="form.processing"
                    :aria-label="`Quitar ${line.model}`"
                    @click="
                        form.lines.splice(index, 1);
                        form.confirmed = false;
                    "
                    >Quitar</Button
                >
            </div>
            <label class="flex min-h-11 items-start gap-3 text-sm">
                <input
                    :id="`quote-confirmed-${intakeId}`"
                    v-model="form.confirmed"
                    type="checkbox"
                    class="mt-1 size-5 shrink-0"
                    required
                    :disabled="form.processing"
                    :aria-invalid="Boolean(form.errors.confirmed)"
                    :aria-describedby="`quote-confirmed-error-${intakeId}`"
                />
                Confirmo los equipos y cantidades para este borrador. La
                revisión eléctrica sigue pendiente.
            </label>
            <p
                :id="`quote-confirmed-error-${intakeId}`"
                class="text-sm text-destructive"
            >
                {{ form.errors.confirmed }}
            </p>
            <Button
                type="submit"
                class="min-h-11"
                :disabled="form.processing"
                >{{
                    form.processing
                        ? 'Consultando precios y guardando…'
                        : 'Guardar borrador con precios actuales'
                }}</Button
            >
        </form>
        <div v-if="draft" class="space-y-3 border-t pt-4">
            <p class="font-medium">
                Borrador v{{ draft.version }} ·
                {{ new Date(draft.created_at).toLocaleString('es-MX') }}
            </p>
            <div
                v-for="line in draft.snapshot.lines"
                :key="line.product_id"
                class="text-sm"
            >
                <p>
                    {{ line.quantity }} × {{ line.model }} ·
                    {{ money(line.line_total_cents) }}
                </p>
                <p
                    v-if="line.availability !== 'available'"
                    class="text-muted-foreground"
                >
                    {{
                        line.availability === 'unknown'
                            ? 'Existencia sin informar.'
                            : 'Existencia insuficiente para esta cantidad.'
                    }}
                </p>
            </div>
            <dl class="space-y-1 text-sm">
                <div class="flex justify-between gap-3">
                    <dt>Subtotal de equipos</dt>
                    <dd>{{ money(draft.snapshot.subtotal_cents) }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt>IVA</dt>
                    <dd>
                        {{
                            draft.snapshot.vat_cents === null
                                ? 'Pendiente de configurar'
                                : money(draft.snapshot.vat_cents)
                        }}
                    </dd>
                </div>
                <div class="flex justify-between gap-3 font-semibold">
                    <dt>Total</dt>
                    <dd>
                        {{
                            draft.snapshot.total_cents === null
                                ? 'Pendiente de IVA'
                                : money(draft.snapshot.total_cents)
                        }}
                    </dd>
                </div>
            </dl>
            <p class="text-sm text-muted-foreground">
                Precios congelados al guardar. Validación eléctrica pendiente.
                Borrador interno sin envío al cliente.
            </p>
        </div>
        <p class="text-sm font-medium">
            La instalación no está incluida. Se cotiza por separado.
        </p>
    </section>
</template>
