<script setup lang="ts">
import DataTable from '@/components/datatable/Main.vue';
import Column from '@/components/datatable/Column.vue';
import type { FetcherResponse } from '@/types/FetcherResponse';
import type { BookingAppointment } from '@/types/BookingAppointment';
import { BookingAppointmentTableService } from '@/services/bookingAppointmentTableService';
import BookingAppointmentFiltros from './BookingAppointmentFiltros.vue';
import BookingAppointmentCard from './BookingAppointmentCard.vue';
import Badge from '@/components/common/Badge.vue';
import { Users } from 'lucide-vue-next';
import { getUserInitials } from '@/utils/getUserInitials';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';

defineProps<{
    resource: FetcherResponse<BookingAppointment>;
}>();

// Servicio que expone estado + handlers
const { filtros, visibleColumns, verUsuarioPerfil, verBooking, getStatusColor, getStatusLabel } = new BookingAppointmentTableService();

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
    <DataTable
        :resource="resource"
        resourcePropName="bookingAppointments"
        use-filters
        :canToggleColumnsVisibility="true"
        v-model:visibleColumns="visibleColumns"
        :responsiveCards="'lg'"
    >
        <!-- Filtros -->
        <template #filters>
            <BookingAppointmentFiltros v-model:filtros="filtros" />
        </template>

        <!-- Card responsivo -->
        <template #responsive-card="{ slotProps }">
            <BookingAppointmentCard :appointment="slotProps" />
        </template>

        <!-- Columna Tutor -->
        <Column header="Tutor">
            <template #body="{ record }">
                <div class="flex items-center gap-3 cursor-pointer group" @click="verUsuarioPerfil(record?.booking?.tutor?.user)">
                    <!-- Avatar -->
                    <Avatar shape="square" size="sm" class="overflow-hidden">
                        <AvatarImage
                            v-if="record?.booking?.tutor?.user?.avatar_url"
                            :src="record.booking.tutor.user.avatar_url"
                            :alt="record?.booking?.tutor?.user?.name ?? 'avatar'"
                            class="h-8 w-8 object-cover"
                        />
                        <AvatarFallback v-else>
                            {{ getUserInitials(record?.booking?.tutor?.user) }}
                        </AvatarFallback>
                    </Avatar>

                    <!-- Nombre -->
                    <div class="min-w-0">
                        <span class="truncate hover:underline group-hover:text-rose-400 dark:group-hover:text-rose-300">
                            {{ record?.booking?.tutor?.user?.name ?? '—' }}
                            {{ record?.booking?.tutor?.user?.surnames ?? '' }}
                        </span>
                    </div>
                </div>
            </template>
        </Column>

        <!-- Columna Niños -->
        <Column header="Niños">
            <template #body="slotProps">
                <Badge
                    v-if="slotProps.record?.children && slotProps.record.children.length > 0"
                    :label="`${slotProps.record.children.length}`"
                    customClass="bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 flex items-center gap-1"
                >
                    <template #icon>
                        <Users class="w-3 h-3" />
                    </template>
                </Badge>
                <span v-else class="text-muted-foreground">0</span>
            </template>
        </Column>

        <!-- Columna Servicio -->
        <Column header="Servicio">
            <template #body="slotProps">
                <a
                    :href="`/bookings/${slotProps.record?.booking_id}`"
                    class="text-primary hover:underline font-mono text-sm"
                    @click.stop="verBooking(slotProps.record?.booking_id)"
                >
                    #{{ slotProps.record?.booking_id }}
                </a>
            </template>
        </Column>

        <!-- Columna Dirección (Zona) -->
        <Column header="Dirección">
            <template #body="slotProps">
                <span class="text-sm">{{ slotProps.record?.addresses?.[0]?.neighborhood ?? '—' }}</span>
            </template>
        </Column>

        <!-- Columna Status -->
        <Column header="Status">
            <template #body="slotProps">
                <Badge :label="getStatusLabel(slotProps.record?.status ?? '')" :customClass="getStatusColor(slotProps.record?.status ?? '')" />
            </template>
        </Column>

        <!-- Columna Start Date -->
        <Column header="Start Date" field="start_date" :sortable="true">
            <template #body="slotProps">
                {{ formatDate(slotProps.record.start_date) }}
            </template>
        </Column>

        <!-- Columna End Date -->
        <Column header="End Date" field="end_date" :sortable="true">
            <template #body="slotProps">
                {{ formatDate(slotProps.record.end_date) }}
            </template>
        </Column>

        <!-- Columna Niñera -->
        <Column header="Niñera">
            <template #body="{ record }">
                <div v-if="record?.nanny" class="flex items-center gap-3 cursor-pointer group" @click="verUsuarioPerfil(record?.nanny?.user)">
                    <!-- Avatar -->
                    <Avatar shape="square" size="sm" class="overflow-hidden">
                        <AvatarImage
                            v-if="record?.nanny?.user?.avatar_url"
                            :src="record.nanny.user.avatar_url"
                            :alt="record?.nanny?.user?.name ?? 'avatar'"
                            class="h-8 w-8 object-cover"
                        />
                        <AvatarFallback v-else>
                            {{ getUserInitials(record?.nanny?.user) }}
                        </AvatarFallback>
                    </Avatar>

                    <!-- Nombre -->
                    <div class="min-w-0">
                        <span class="truncate hover:underline group-hover:text-rose-400 dark:group-hover:text-rose-300">
                            {{ record?.nanny?.user?.name ?? '—' }}
                            {{ record?.nanny?.user?.surnames ?? '' }}
                        </span>
                    </div>
                </div>
                <span v-else class="text-muted-foreground">Sin asignar</span>
            </template>
        </Column>
    </DataTable>
</template>
