<script setup lang="ts">
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
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { getUserInitials } from '@/utils/getUserInitials';

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
} = new BookingTableService();
</script>

<template>
    <!-- Card with styling matching desktop table rows -->
    <div class="bg-white/50 dark:bg-background/50 border border-foreground/20 rounded-lg p-3 space-y-3 relative">
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

        <!-- ID and Tutor -->
        <div class="flex items-center gap-3 pr-8">
            <div class="flex items-center gap-3">
                <Avatar shape="square" size="sm" class="overflow-hidden">
                    <AvatarImage
                        v-if="props.booking.tutor?.user?.avatar_url"
                        :src="props.booking.tutor.user.avatar_url"
                        :alt="props.booking.tutor?.user?.name ?? 'avatar'"
                        class="h-8 w-8 object-cover"
                    />
                    <AvatarFallback v-else>
                        {{ getUserInitials(props.booking.tutor?.user) }}
                    </AvatarFallback>
                </Avatar>

                <div class="min-w-0">
                    <div class="text-sm font-semibold truncate text-foreground/80">
                        {{ props.booking.tutor?.user?.name ?? '—' }} {{ props.booking.tutor?.user?.surnames ?? '' }}
                    </div>
                    <div class="font-mono text-xs text-muted-foreground">
                        #{{ props.booking.id }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Tipo badge -->
        <div class="flex items-center gap-2">
            <span class="text-xs text-muted-foreground font-medium">Tipo:</span>
            <Badge
                v-if="props.booking.recurrent"
                label="Recurrente"
                customClass="bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:border dark:border-indigo-200 dark:text-indigo-200"
            />
            <Badge v-else label="Fijo" customClass="bg-gray-100 text-gray-800 dark:bg-gray-800/40 dark:border dark:border-gray-100 dark:border-gray-700 dark:text-gray-200" />
        </div>

        <!-- Citas -->
        <div class="flex items-center gap-2">
            <span class="text-xs text-muted-foreground font-medium">Citas:</span>
            <Badge
                v-if="props.booking?.booking_appointments && props.booking.booking_appointments.length > 0"
                :label="`${props.booking.booking_appointments.length}`"
                customClass="bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 flex items-center gap-1"
            >
                <template #icon>
                    <Calendar class="w-3 h-3" />
                </template>
            </Badge>
            <span v-else class="text-sm text-muted-foreground">Sin citas</span>
        </div>

        <!-- Fecha de creación -->
        <div class="flex items-center gap-2 text-sm text-foreground/80">
            <span class="text-xs text-muted-foreground font-medium">Creado:</span>
            <span>{{ new Date(props.booking.created_at).toLocaleDateString('es-ES') }}</span>
        </div>

        <!-- Modal eliminar -->
        <DeleteModal
            v-model:show="modalEliminarBooking"
            :message="`¿Estás seguro de eliminar esta reserva?`"
            :onConfirm="eliminarBooking"
            :onCancel="cerrarModalEliminarBooking"
            confirmText="Sí, eliminar"
            cancelText="No, cancelar"
        />
    </div>
</template>
