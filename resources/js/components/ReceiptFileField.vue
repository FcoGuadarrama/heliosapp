<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

withDefaults(
    defineProps<{
        name?: string;
        id?: string;
        error?: string;
        maxSizeMb?: number;
    }>(),
    { name: 'file', id: 'receipt-file', maxSizeMb: 10 },
);
</script>

<template>
    <div class="grid gap-2">
        <Label :for="id">Recibo CFE</Label>
        <input
            :id="id"
            type="file"
            :name="name"
            accept="application/pdf,image/jpeg,image/png,image/webp"
            required
            :aria-invalid="!!error"
            :aria-describedby="`${id}-hint ${id}-error`"
            class="w-full rounded-md border px-3 py-2 text-sm file:mr-3 file:rounded file:border-0 file:bg-secondary file:px-3 file:py-1 file:text-secondary-foreground focus-visible:outline-2 focus-visible:outline-ring"
        />
        <p :id="`${id}-hint`" class="text-sm text-muted-foreground">
            PDF, JPG, PNG o WebP. Máximo {{ maxSizeMb }} MB. El archivo se
            guardará para revisión; esta carga no extrae automáticamente sus
            datos.
        </p>
        <InputError :id="`${id}-error`" :message="error" />
    </div>
</template>
