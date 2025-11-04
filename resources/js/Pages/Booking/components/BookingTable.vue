<script setup lang="ts">
import DataTable from '@/components/datatable/Main.vue';
import Column from '@/components/datatable/Column.vue';
import type { FetcherResponse } from '@/types/FetcherResponse';
import type { Booking } from '@/types/Booking';
import { BookingTableService } from '@/services/bookingTableService';
import BookingFiltros from './BookingFiltros.vue';
import BookingCard from './BookingCard.vue';
import DeleteModal from '@/components/common/DeleteModal.vue';
import Badge from '@/components/common/Badge.vue';
import { Calendar } from 'lucide-vue-next';

defineProps<{
    resource: FetcherResponse<Booking>;
}>();

// Servicio que expone estado + handlers
const {
    modalEliminarBooking,
    filtros,
    visibleColumns,
    verBooking,
    editarBooking,
    abrirModalEliminarBooking,
    cerrarModalEliminarBooking,
    eliminarBooking,
    getStatusColor,
    getStatusLabel,
} = new BookingTableService();
</script>

<template>
    <DataTable
        :resource="resource"
        resourcePropName="bookings"
        use-filters
        :canToggleColumnsVisibility="true"
        v-model:visibleColumns="visibleColumns"
        :responsiveCards="'lg'"
    >
        <!-- Filtros -->
        <template #filters>
            <BookingFiltros v-model:filtros="filtros" />
        </template>

        <!-- Card responsivo -->
        <template #responsive-card="{ slotProps }">
            <BookingCard :booking="slotProps" />
        </template>

        <!-- Columna ID -->
        <Column header="ID" field="id" :sortable="true">
            <template #body="slotProps">
                <span class="font-mono text-sm">#{{ slotProps.record.id }}</span>
            </template>
        </Column>

        <!-- Columna Tutor -->
        <Column header="Tutor">
            <template #body="slotProps">
                <a
                    :href="`/tutors/${slotProps.record?.tutor?.id}`"
                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:underline"
                >
                    {{ slotProps.record?.tutor?.user?.name ?? '—' }} {{ slotProps.record?.tutor?.user?.surnames ?? '' }}
                </a>
            </template>
        </Column>

        <!-- Columna Tipo -->
        <Column header="Tipo">
            <template #body="slotProps">
                <Badge
                    v-if="slotProps.record.recurrent"
                    label="Recurrente"
                    customClass="bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200"
                />
                <Badge v-else label="Fijo" customClass="bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200" />
            </template>
        </Column>

        <!-- Columna Citas con Badge e Ícono -->
        <Column header="No. Citas">
            <template #body="slotProps">
                <Badge
                    v-if="slotProps.record?.booking_appointments && slotProps.record.booking_appointments.length > 0"
                    :label="`${slotProps.record.booking_appointments.length}`"
                    customClass="bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 flex items-center gap-1"
                >
                    <template #icon>
                        <Calendar class="w-3 h-3" />
                    </template>
                </Badge>
                <span v-else class="text-muted-foreground">Sin citas</span>
            </template>
        </Column>

        <!-- Columna Fecha Creación -->
        <Column header="Fecha Creación" field="created_at" :sortable="true">
            <template #body="slotProps">
                {{ new Date(slotProps.record.created_at).toLocaleDateString('es-ES') }}
            </template>
        </Column>

        <!-- Acciones con botones más grandes -->
        <Column header="Acciones" field="id">
            <template #body="slotProps">
                <div class="flex gap-3">
                    <div
                        @click="verBooking(slotProps.record)"
                        class="flex justify-center items-center w-max text-green-600 dark:text-green-500 hover:text-green-600/80 dark:hover:text-green-400 hover:cursor-pointer"
                        title="Ver detalles"
                    >
                        <Icon icon="mdi:eye-outline" :size="22" />
                    </div>

                    <div
                        @click="editarBooking(slotProps.record)"
                        class="flex justify-center items-center w-max text-blue-600 dark:text-blue-500 hover:text-blue-600/80 dark:hover:text-blue-400 hover:cursor-pointer"
                        title="Editar booking"
                    >
                        <Icon icon="mdi:edit-outline" :size="22" />
                    </div>

                    <div
                        @click="abrirModalEliminarBooking(slotProps.record)"
                        class="flex justify-center items-center w-max text-rose-600 dark:text-rose-500 hover:text-rose-600/80 dark:hover:text-rose-400 hover:cursor-pointer"
                        title="Eliminar booking"
                    >
                        <Icon icon="fluent:delete-12-regular" :size="22" />
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
