<script setup lang="ts">
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

export interface FiltrosBooking {
    recurrent: string | null;
    status: string | null;
    date_from: string | null;
    date_to: string | null;
}

const filtros = defineModel<FiltrosBooking>('filtros', {
    default: {
        recurrent: null,
        status: null,
        date_from: null,
        date_to: null,
    },
});
</script>

<template>
    <div class=" w-full">
        <!-- Filtro Recurrente -->
        <div class="w-full">
            <div class="flex flex-col">
                <label for="filtro-recurrent" class="mb-1 ml-1"> Tipo de Servicio </label>

                <Select v-model="filtros.recurrent">
                    <SelectTrigger>
                        <SelectValue placeholder="Todos" />
                    </SelectTrigger>

                    <SelectContent>
                        <SelectGroup>
                            <SelectItem value="true">Recurrente</SelectItem>
                            <SelectItem value="false">Fijo</SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>
        </div>
    </div>
</template>
