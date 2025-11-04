<script setup lang="ts">
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { BookingStatus, BookingStatusLabels } from '@/enums/booking-status.enum';

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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 w-full">
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

        <!-- Filtro Estado -->
        <div class="w-full">
            <div class="flex flex-col">
                <label for="filtro-status" class="mb-1 ml-1"> Estado </label>

                <Select v-model="filtros.status">
                    <SelectTrigger>
                        <SelectValue placeholder="Todos los estados" />
                    </SelectTrigger>

                    <SelectContent>
                        <SelectGroup>
                            <SelectItem v-for="(label, value) in BookingStatusLabels" :key="value" :value="value">
                                {{ label }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>
        </div>
    </div>
</template>
