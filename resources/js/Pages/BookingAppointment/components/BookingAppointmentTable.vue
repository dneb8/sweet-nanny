<script setup lang="ts">
import { ref } from 'vue';
import DataTable from '@/components/datatable/Main.vue';
import Column from '@/components/datatable/Column.vue';
import type { FetcherResponse } from '@/types/FetcherResponse';
import type { BookingAppointment } from '@/types/BookingAppointment';
import type { Tutor } from '@/types/Tutor';
import { BookingAppointmentTableService } from '@/services/bookingAppointmentTableService';
import BookingAppointmentFiltros from './BookingAppointmentFiltros.vue';
import BookingAppointmentCard from './BookingAppointmentCard.vue';
import TutorDetailDialog from './TutorDetailDialog.vue';
import ReviewFormDialog from '@/Pages/Review/components/ReviewFormDialog.vue';
import DeleteModal from '@/components/common/DeleteModal.vue';
import Badge from '@/components/common/Badge.vue';
import { Users, Star, Check, X, UserX } from 'lucide-vue-next';
import { getUserInitials } from '@/utils/getUserInitials';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { router } from '@inertiajs/vue3';
import { getBookingStatusBadgeClass, getBookingStatusLabelByString } from '@/enums/booking/status.enum';
import { getZoneLabel, getZoneBadgeClass } from '@/enums/addresses/zone.enum';

defineProps<{
    resource: FetcherResponse<BookingAppointment>;
}>();

// Servicio que expone estado + handlers
const { filtros, visibleColumns, verBooking } = new BookingAppointmentTableService();

// Tutor dialog state
const tutorDialogOpen = ref(false);
const selectedTutor = ref<Tutor | null>(null);

// Review dialog state
const reviewDialogOpen = ref(false);
const selectedTutorId = ref<number | string | null>(null);
const selectedTutorName = ref<string>('');

// Modal states
const showAcceptModal = ref(false);
const showRejectModal = ref(false);
const showUnassignModal = ref(false);
const currentAppointmentId = ref<number | null>(null);

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};

const openTutorDialog = (tutor: Tutor | undefined) => {
    if (tutor) {
        selectedTutor.value = tutor;
        tutorDialogOpen.value = true;
    }
};

const openReviewModal = (appointment: BookingAppointment) => {
    if (appointment?.booking?.tutor?.id) {
        selectedTutorId.value = appointment.booking.tutor.id;
        selectedTutorName.value = `${appointment.booking.tutor.user?.name || ''} ${appointment.booking.tutor.user?.surnames || ''}`.trim();
        reviewDialogOpen.value = true;
    }
};

const openAcceptModal = (appointmentId: number) => {
    currentAppointmentId.value = appointmentId;
    showAcceptModal.value = true;
};

const confirmAccept = () => {
    if (currentAppointmentId.value) {
        router.patch(
            route('booking-appointments.accept', currentAppointmentId.value),
            {},
            {
                preserveScroll: true,
            }
        );
    }
    showAcceptModal.value = false;
};

const openRejectModal = (appointmentId: number) => {
    currentAppointmentId.value = appointmentId;
    showRejectModal.value = true;
};

const confirmReject = () => {
    if (currentAppointmentId.value) {
        router.patch(
            route('booking-appointments.reject', currentAppointmentId.value),
            {},
            {
                preserveScroll: true,
            }
        );
    }
    showRejectModal.value = false;
};

const openUnassignModal = (appointmentId: number) => {
    currentAppointmentId.value = appointmentId;
    showUnassignModal.value = true;
};

const confirmUnassign = () => {
    if (currentAppointmentId.value) {
        router.patch(
            route('booking-appointments.unassign-nanny', currentAppointmentId.value),
            {},
            {
                preserveScroll: true,
            }
        );
    }
    showUnassignModal.value = false;
};

const handleReviewSaved = () => {
    // Refresh the page to show updated data
    router.reload({ only: ['bookingAppointments'] });
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
                <div class="flex items-center gap-3 cursor-pointer group" @click="openTutorDialog(record?.booking?.tutor)">
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
                <span
                    class="text-primary hover:underline font-mono text-sm cursor-pointer"
                    @click.stop="verBooking(slotProps.record?.booking_id)"
                >
                    #{{ slotProps.record?.booking_id }}
                </span>
            </template>
        </Column>

        <!-- Columna Zona -->
        <Column header="Zona">
            <template #body="slotProps">
                <Badge
                    v-if="slotProps.record?.addresses?.[0]?.zone"
                    :label="getZoneLabel(slotProps.record.addresses[0].zone)"
                    :customClass="getZoneBadgeClass(slotProps.record.addresses[0].zone)"
                />
                <span v-else class="text-sm text-muted-foreground">—</span>
            </template>
        </Column>

        <!-- Columna Status -->
        <Column header="Status">
            <template #body="slotProps">
                <Badge
                    :label="getBookingStatusLabelByString(slotProps.record?.status ?? '')"
                    :customClass="getBookingStatusBadgeClass(slotProps.record?.status ?? '')"
                />
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

        <!-- Columna Acciones -->
        <Column header="Acciones">
            <template #body="{ record }">
                <div class="flex gap-2">
                    <!-- Aceptar (pending → confirmed) - Only for pending with nanny assigned -->
                    <div
                        v-if="record?.status === 'pending' && record?.nanny_id"
                        @click="openAcceptModal(record.id)"
                        class="flex justify-center items-center w-max text-green-600 dark:text-green-500 hover:text-green-600/80 dark:hover:text-green-400 hover:cursor-pointer"
                        title="Aceptar solicitud"
                    >
                        <Check class="w-5 h-5" />
                    </div>

                    <!-- Rechazar (pending with nanny) -->
                    <div
                        v-if="record?.status === 'pending' && record?.nanny_id"
                        @click="openRejectModal(record.id)"
                        class="flex justify-center items-center w-max text-rose-600 dark:text-rose-500 hover:text-rose-600/80 dark:hover:text-rose-400 hover:cursor-pointer"
                        title="Rechazar solicitud"
                    >
                        <X class="w-5 h-5" />
                    </div>

                    <!-- Cancelar niñera (confirmed → pending, unassign nanny) -->
                    <div
                        v-if="record?.status === 'confirmed' && record?.nanny_id"
                        @click="openUnassignModal(record.id)"
                        class="flex justify-center items-center w-max text-orange-600 dark:text-orange-500 hover:text-orange-600/80 dark:hover:text-orange-400 hover:cursor-pointer"
                        title="Cancelar niñera"
                    >
                        <UserX class="w-5 h-5" />
                    </div>

                    <!-- Calificar tutor -->
                    <div
                        v-if="record?.status === 'completed'"
                        @click="openReviewModal(record)"
                        class="flex justify-center items-center w-max text-yellow-600 dark:text-yellow-500 hover:text-yellow-600/80 dark:hover:text-yellow-400 hover:cursor-pointer"
                        title="Calificar tutor"
                    >
                        <Star class="w-5 h-5" />
                    </div>

                    <!-- No actions available -->
                    <span v-if="!record?.nanny_id || (record?.status !== 'pending' && record?.status !== 'confirmed' && record?.status !== 'completed')" class="text-sm text-muted-foreground">
                        —
                    </span>
                </div>
            </template>
        </Column>
    </DataTable>

    <!-- Tutor Detail Dialog -->
    <TutorDetailDialog v-model:open="tutorDialogOpen" :tutor="selectedTutor" @close="tutorDialogOpen = false" />

    <!-- Review Form Dialog -->
    <ReviewFormDialog
        v-model:open="reviewDialogOpen"
        reviewable-type="tutor"
        :reviewable-id="selectedTutorId!"
        :reviewable-name="selectedTutorName"
        @saved="handleReviewSaved"
        @close="reviewDialogOpen = false"
    />

    <!-- Accept Modal -->
    <DeleteModal
        :show="showAcceptModal"
        @update:show="showAcceptModal = $event"
        message="¿Estás seguro de que quieres aceptar esta solicitud? La cita será confirmada."
        :onConfirm="confirmAccept"
        :onCancel="() => (showAcceptModal = false)"
    />

    <!-- Reject Modal -->
    <DeleteModal
        :show="showRejectModal"
        @update:show="showRejectModal = $event"
        message="¿Estás seguro de que quieres rechazar esta solicitud? La niñera será desasignada."
        :onConfirm="confirmReject"
        :onCancel="() => (showRejectModal = false)"
    />

    <!-- Unassign Nanny Modal -->
    <DeleteModal
        :show="showUnassignModal"
        @update:show="showUnassignModal = $event"
        message="¿Estás seguro de que quieres cancelar la niñera asignada? La cita volverá a estado pendiente."
        :onConfirm="confirmUnassign"
        :onCancel="() => (showUnassignModal = false)"
    />
</template>
