<script setup lang="ts">
import DataTable from '@/components/datatable/Main.vue';
import Column from '@/components/datatable/Column.vue';
import type { FetcherResponse } from '@/types/FetcherResponse';
import type { Booking } from '@/types/Booking';
import { BookingTableService } from '@/services/bookingTableService';
import DeleteModal from '@/components/common/DeleteModal.vue';

defineProps<{
    resource: FetcherResponse<Booking>;
}>();

// Servicio que expone estado + handlers
const {
    bookingEliminar,
    modalEliminarBooking,
    visibleColumns,
    verBooking,
    editarBooking,
    abrirModalEliminarBooking,
    cerrarModalEliminarBooking,
    eliminarBooking,
} = new BookingTableService();
</script>

<template>
    <DataTable :resource="resource" resourcePropName="bookings" :canToggleColumnsVisibility="true" v-model:visibleColumns="visibleColumns">
        <!-- Columna Tutor -->
        <Column header="Tutor">
            <template #body="slotProps">
                {{ slotProps.record?.tutor?.user?.name ?? '—' }}
                {{ slotProps.record?.tutor?.user?.surnames ?? '' }}
            </template>
        </Column>

        <!-- Columna Dirección -->
        <Column header="Dirección">
            <template #body="slotProps">
                <div v-if="slotProps.record?.address">
                    {{ slotProps.record.address.street }}, {{ slotProps.record.address.neighborhood }}
                </div>
                <span v-else>—</span>
            </template>
        </Column>

        <!-- Columna Niños -->
        <Column header="Niños">
            <template #body="slotProps">
                <span v-if="slotProps.record?.children && slotProps.record.children.length > 0">
                    {{ slotProps.record.children.length }} niño(s)
                </span>
                <span v-else>—</span>
            </template>
        </Column>

        <!-- Columna Citas -->
        <Column header="Citas">
            <template #body="slotProps">
                <div v-if="slotProps.record?.booking_appointments && slotProps.record.booking_appointments.length > 0">
                    {{ slotProps.record.booking_appointments.length }} cita(s)
                    <div v-if="slotProps.record.booking_appointments[0]?.nanny" class="text-xs text-muted-foreground">
                        Niñera: {{ slotProps.record.booking_appointments[0].nanny.user?.name ?? '—' }}
                    </div>
                </div>
                <span v-else>Sin citas</span>
            </template>
        </Column>

        <!-- Columna Fecha Creación -->
        <Column header="Fecha Creación" field="created_at" :sortable="true">
            <template #body="slotProps">
                {{ new Date(slotProps.record.created_at).toLocaleDateString('es-ES') }}
            </template>
        </Column>

        <!-- Acciones -->
        <Column header="Acciones" field="id">
            <template #body="slotProps">
                <div class="flex gap-2">
                    <div
                        @click="verBooking(slotProps.record)"
                        class="flex justify-center items-center w-max text-green-600 dark:text-green-500 hover:text-green-600/80 dark:hover:text-green-400 hover:cursor-pointer"
                        title="Ver detalles"
                    >
                        <Icon icon="mdi:eye-outline" :size="20" />
                    </div>

                    <div
                        @click="editarBooking(slotProps.record)"
                        class="flex justify-center items-center w-max text-blue-600 dark:text-blue-500 hover:text-blue-600/80 dark:hover:text-blue-400 hover:cursor-pointer"
                        title="Editar booking"
                    >
                        <Icon icon="mdi:edit-outline" :size="20" />
                    </div>

                    <div
                        @click="abrirModalEliminarBooking(slotProps.record)"
                        class="flex justify-center items-center w-max text-rose-600 dark:text-rose-500 hover:text-rose-600/80 dark:hover:text-rose-400 hover:cursor-pointer"
                        title="Eliminar booking"
                    >
                        <Icon icon="fluent:delete-12-regular" :size="20" />
                    </div>
                </div>
            </template>
        </Column>
    </DataTable>

    <!-- Modal de confirmación de eliminación -->
    <DeleteModal
        v-model:show="modalEliminarBooking"
        :message="`¿Estás seguro de eliminar esta reserva?`"
        :onConfirm="eliminarBooking"
        :onCancel="cerrarModalEliminarBooking"
        confirmText="Sí, eliminar"
        cancelText="No, cancelar"
    />
</template>
