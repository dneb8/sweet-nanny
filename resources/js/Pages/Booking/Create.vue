<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import BookingForm from './partials/BookingForm.vue';
import Heading from '@/components/Heading.vue';
</script>

<template>
     <Head title="Nuevo Servicio" />

     <Heading icon='fluent:calendar-add-24-regular' title="Crear Servicio"/>

     <BookingForm/>
</template>