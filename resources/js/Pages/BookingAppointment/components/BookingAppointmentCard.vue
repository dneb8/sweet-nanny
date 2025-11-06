<script setup lang="ts">
import { BookingAppointment } from '@/types/BookingAppointment';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { getUserInitials } from '@/utils/getUserInitials';
import Badge from '@/components/common/Badge.vue';
import { BookingAppointmentTableService } from '@/services/bookingAppointmentTableService';
import { Users } from 'lucide-vue-next';

defineProps<{
    appointment: BookingAppointment;
}>();

const service = new BookingAppointmentTableService();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-4 space-y-3">
        <!-- Tutor -->
        <div class="flex items-center gap-3">
            <Avatar shape="square" size="sm" class="overflow-hidden">
                <AvatarImage
                    v-if="appointment?.booking?.tutor?.user?.avatar_url"
                    :src="appointment.booking.tutor.user.avatar_url"
                    :alt="appointment?.booking?.tutor?.user?.name ?? 'avatar'"
                    class="h-8 w-8 object-cover"
                />
                <AvatarFallback v-else>
                    {{ getUserInitials(appointment?.booking?.tutor?.user) }}
                </AvatarFallback>
            </Avatar>
            <div>
                <div class="text-sm font-medium">Tutor</div>
                <div class="text-sm text-muted-foreground">
                    {{ appointment?.booking?.tutor?.user?.name ?? '—' }}
                    {{ appointment?.booking?.tutor?.user?.surnames ?? '' }}
                </div>
            </div>
        </div>

        <!-- Servicio y Niños -->
        <div class="grid grid-cols-2 gap-2">
            <div>
                <div class="text-sm font-medium">Servicio</div>
                <span
                    class="text-sm text-primary hover:underline cursor-pointer"
                    @click.stop="service.verBooking(appointment?.booking_id)"
                >
                    #{{ appointment?.booking_id }}
                </span>
            </div>
            <div>
                <div class="text-sm font-medium">Niños</div>
                <Badge
                    v-if="appointment?.children && appointment.children.length > 0"
                    :label="`${appointment.children.length}`"
                    customClass="bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 flex items-center gap-1"
                >
                    <template #icon>
                        <Users class="w-3 h-3" />
                    </template>
                </Badge>
                <span v-else class="text-sm text-muted-foreground">0</span>
            </div>
        </div>

        <!-- Dirección y Estado -->
        <div class="grid grid-cols-2 gap-2">
            <div>
                <div class="text-sm font-medium">Zona</div>
                <div class="text-sm text-muted-foreground">
                    {{ appointment?.addresses?.[0]?.neighborhood ?? '—' }}
                </div>
            </div>
            <div>
                <div class="text-sm font-medium">Estado</div>
                <Badge :label="service.getStatusLabel(appointment?.status ?? '')" :customClass="service.getStatusColor(appointment?.status ?? '')" />
            </div>
        </div>

        <!-- Fechas -->
        <div class="grid grid-cols-2 gap-2">
            <div>
                <div class="text-sm font-medium">Inicio</div>
                <div class="text-sm text-muted-foreground">{{ formatDate(appointment?.start_date) }}</div>
            </div>
            <div>
                <div class="text-sm font-medium">Fin</div>
                <div class="text-sm text-muted-foreground">{{ formatDate(appointment?.end_date) }}</div>
            </div>
        </div>

        <!-- Niñera -->
        <div v-if="appointment?.nanny" class="flex items-center gap-3 pt-2 border-t">
            <Avatar shape="square" size="sm" class="overflow-hidden">
                <AvatarImage
                    v-if="appointment?.nanny?.user?.avatar_url"
                    :src="appointment.nanny.user.avatar_url"
                    :alt="appointment?.nanny?.user?.name ?? 'avatar'"
                    class="h-8 w-8 object-cover"
                />
                <AvatarFallback v-else>
                    {{ getUserInitials(appointment?.nanny?.user) }}
                </AvatarFallback>
            </Avatar>
            <div>
                <div class="text-sm font-medium">Niñera</div>
                <div class="text-sm text-muted-foreground">
                    {{ appointment?.nanny?.user?.name ?? '—' }}
                    {{ appointment?.nanny?.user?.surnames ?? '' }}
                </div>
            </div>
        </div>
        <div v-else class="text-sm text-muted-foreground pt-2 border-t">Sin niñera asignada</div>
    </div>
</template>
