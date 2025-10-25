<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { FetcherResponse } from '@/types/FetcherResponse';
import type { Booking } from '@/types/Booking';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import BookingTable from './components/BookingTable.vue';

defineProps<{
    bookings: FetcherResponse<Booking>;
}>();
</script>

<template>
    <Head title="Servicios" />

    <div class="mb-4 flex items-center justify-between">
        <Heading icon="ri:calendar-schedule-line" title="Listado de Servicios" />
        <Link :href="route('bookings.create')">
            <Button class="gap-2">
                <Icon icon="ri:calendar-event-line" width="18" height="18" />
                Crear Servicio
            </Button>
        </Link>
    </div>

    <BookingTable :resource="bookings" />
</template>

