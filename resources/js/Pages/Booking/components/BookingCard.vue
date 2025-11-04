<script setup lang="ts">
import { Card, CardHeader, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Icon } from '@iconify/vue';
import { MoreVertical, Calendar } from 'lucide-vue-next';
import type { Booking } from '@/types/Booking';
import { BookingTableService } from '@/services/bookingTableService';
import DeleteModal from '@/components/common/DeleteModal.vue';
import Badge from '@/components/common/Badge.vue';

const props = defineProps<{
    booking: Booking;
}>();

const {
    modalEliminarBooking,
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
    <Card class="relative overflow-hidden">
        <!-- Menú de acciones -->
        <div class="absolute top-2 right-2 z-20">
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="ghost" size="icon" class="h-8 w-8 p-0">
                        <MoreVertical class="h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>

                <DropdownMenuContent align="end" class="w-44">
                    <DropdownMenuGroup>
                        <!-- Ver detalles -->
                        <DropdownMenuItem @click="verBooking(props.booking)" class="group text-muted-foreground hover:bg-muted">
                            <Icon icon="mdi:eye-outline" class="w-4 h-4 mr-2 text-green-600" />
                            Ver detalles
                        </DropdownMenuItem>

                        <!-- Editar -->
                        <DropdownMenuItem @click="editarBooking(props.booking)" class="group text-muted-foreground hover:bg-muted">
                            <Icon icon="mdi:pencil-outline" class="w-4 h-4 mr-2 text-sky-600" />
                            Editar
                        </DropdownMenuItem>
                    </DropdownMenuGroup>

                    <DropdownMenuSeparator />

                    <!-- Eliminar -->
                    <DropdownMenuItem @click="abrirModalEliminarBooking(props.booking)" class="group text-muted-foreground hover:bg-muted">
                        <Icon icon="mdi:trash-can-outline" class="w-4 h-4 mr-2 text-rose-600" />
                        Eliminar
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>

        <!-- Cabecera -->
        <CardHeader
            class="flex flex-row gap-4 items-start px-4 transition-transform duration-200 hover:scale-105 cursor-pointer"
            @click="verBooking(props.booking)"
        >
            <!-- Avatar/Icon -->
            <div class="flex-none w-20 flex flex-col items-center">
                <div class="w-16 h-16 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center border overflow-hidden">
                    <Icon icon="mdi:calendar-check" class="w-8 h-8 text-slate-400" />
                </div>
            </div>

            <!-- Info booking -->
            <div class="flex-1 min-w-0">
                <!-- Badge de estado -->
                <Badge :label="getStatusLabel(props.booking.status)" :customClass="getStatusColor(props.booking.status)" />

                <!-- Tutor -->
                <div class="mt-2 flex items-center gap-2 min-w-0">
                    <h3 class="text-sm font-semibold truncate">
                        {{ props.booking.tutor?.user?.name ?? '—' }} {{ props.booking.tutor?.user?.surnames ?? '' }}
                    </h3>
                </div>

                <!-- Recurrente badge -->
                <div class="mt-1 flex items-center gap-2">
                    <span v-if="props.booking.recurrent" class="text-xs px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                        Recurrente
                    </span>
                    <span v-else class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                        Fijo
                    </span>
                </div>
            </div>
        </CardHeader>

        <!-- Contenido: detalles del booking -->
        <CardContent class="px-4 pb-4 space-y-2">
            <!-- Descripción -->
            <div v-if="props.booking.description" class="text-sm text-muted-foreground">
                {{ props.booking.description }}
            </div>

            <!-- ID del booking -->
            <div class="flex items-center gap-2 text-xs font-mono text-muted-foreground">
                ID: #{{ props.booking.id }}
            </div>

            <!-- Citas -->
            <div class="flex items-center gap-2 text-xs">
                <Calendar class="w-3 h-3 text-muted-foreground" />
                <span>{{ props.booking.booking_appointments?.length ?? 0 }} cita(s)</span>
            </div>

            <!-- Fecha de creación -->
            <div class="text-xs text-muted-foreground pt-1">
                Creado: {{ new Date(props.booking.created_at).toLocaleDateString('es-ES') }}
            </div>
        </CardContent>

        <!-- Modal eliminar -->
        <DeleteModal
            v-model:show="modalEliminarBooking"
            :message="`¿Estás seguro de eliminar esta reserva?`"
            :onConfirm="eliminarBooking"
            :onCancel="cerrarModalEliminarBooking"
            confirmText="Sí, eliminar"
            cancelText="No, cancelar"
        />
    </Card>
</template>
