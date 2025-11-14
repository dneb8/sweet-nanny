<script setup lang="ts">
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

export interface FiltrosBookingAppointment {
    status: string | null;
}

const filtros = defineModel<FiltrosBookingAppointment>('filtros', {
    default: {
        status: null,
    },
});

// Status options for appointments
const statusOptions = [
    { value: 'draft', label: 'Borrador' },
    { value: 'pending', label: 'Pendiente' },
    { value: 'confirmed', label: 'Confirmado' },
    { value: 'completed', label: 'Completado' },
    { value: 'cancelled', label: 'Cancelado' },
];
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 w-full">
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
                            <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>
        </div>
    </div>
</template>
