<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { FetcherResponse } from '@/types/FetcherResponse';
import type { BookingAppointment } from '@/types/BookingAppointment';
import Heading from '@/components/Heading.vue';
import BookingAppointmentTable from './components/BookingAppointmentTable.vue';

defineProps<{
    bookingAppointments: FetcherResponse<BookingAppointment>;
}>();
</script>

<template>
    <Head title="Citas" />

    <div class="mb-4 flex items-center justify-between">
        <Heading icon="ph:calendar-check" title="Listado de Citas" />
    </div>
    <BookingAppointmentTable :resource="bookingAppointments" />
</template>
