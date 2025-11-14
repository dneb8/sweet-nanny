<script setup lang="ts">
import { BookingAppointment } from '@/types/BookingAppointment';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { getUserInitials } from '@/utils/getUserInitials';
import Badge from '@/components/common/Badge.vue';
import { BookingAppointmentTableService } from '@/services/bookingAppointmentTableService';
import { Users } from 'lucide-vue-next';
import { getBookingStatusBadgeClass, getBookingStatusLabelByString } from '@/enums/booking/status.enum';
import { getZoneLabel, getZoneBadgeClass } from '@/enums/addresses/zone.enum';

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
    <!-- Card with styling matching desktop table rows -->
    <div class="bg-white/50 dark:bg-background/50 border border-foreground/20 rounded-lg p-3 space-y-3">
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
            <div class="min-w-0">
                <div class="text-xs text-muted-foreground font-medium">Tutor</div>
                <div class="text-sm text-foreground/80 truncate">
                    {{ appointment?.booking?.tutor?.user?.name ?? '—' }}
                    {{ appointment?.booking?.tutor?.user?.surnames ?? '' }}
                </div>
            </div>
        </div>

        <!-- Servicio y Niños -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <div class="text-xs text-muted-foreground font-medium">Servicio</div>
                <span
                    class="text-sm text-primary hover:underline cursor-pointer font-mono"
                    @click.stop="service.verBooking(appointment?.booking_id)"
                >
                    #{{ appointment?.booking_id }}
                </span>
            </div>
            <div>
                <div class="text-xs text-muted-foreground font-medium">Niños</div>
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

        <!-- Zona y Estado -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <div class="text-xs text-muted-foreground font-medium">Zona</div>
                <Badge
                    v-if="appointment?.addresses?.[0]?.zone"
                    :label="getZoneLabel(appointment.addresses[0].zone)"
                    :customClass="getZoneBadgeClass(appointment.addresses[0].zone)"
                />
                <span v-else class="text-sm text-muted-foreground">—</span>
            </div>
            <div>
                <div class="text-xs text-muted-foreground font-medium">Estado</div>
                <Badge :label="getBookingStatusLabelByString(appointment?.status ?? '')" :customClass="getBookingStatusBadgeClass(appointment?.status ?? '')" />
            </div>
        </div>

        <!-- Fechas -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <div class="text-xs text-muted-foreground font-medium">Inicio</div>
                <div class="text-sm text-foreground/80">{{ formatDate(appointment?.start_date) }}</div>
            </div>
            <div>
                <div class="text-xs text-muted-foreground font-medium">Fin</div>
                <div class="text-sm text-foreground/80">{{ formatDate(appointment?.end_date) }}</div>
            </div>
        </div>

        <!-- Niñera -->
        <div v-if="appointment?.nanny" class="flex items-center gap-3 pt-2 border-t border-foreground/20">
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
            <div class="min-w-0">
                <div class="text-xs text-muted-foreground font-medium">Niñera</div>
                <div class="text-sm text-foreground/80 truncate">
                    {{ appointment?.nanny?.user?.name ?? '—' }}
                    {{ appointment?.nanny?.user?.surnames ?? '' }}
                </div>
            </div>
        </div>
        <div v-else class="text-sm text-muted-foreground pt-2 border-t border-foreground/20">Sin niñera asignada</div>
    </div>
</template>
