<script setup lang="ts">
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

export interface FiltrosReview {
    approved: string | null;
}

const filtros = defineModel<FiltrosReview>('filtros', {
    default: {
        approved: null,
    },
});
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 w-full">
        <div class="w-full">
            <div class="flex flex-col">
                <label for="filtro-approved" class="mb-1 ml-1"> Estado de Aprobación </label>

                <Select v-model="filtros.approved">
                    <SelectTrigger>
                        <SelectValue placeholder="Selecciona estado" />
                    </SelectTrigger>

                    <SelectContent>
                        <SelectGroup>
                            <SelectItem value="true">Aprobados</SelectItem>
                            <SelectItem value="false">No Aprobados</SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>
        </div>
    </div>
</template>
