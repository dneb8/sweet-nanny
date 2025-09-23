<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import BookingForm from './partials/BookingForm.vue';
import Heading from '@/components/Heading.vue';
</script>

<template>
     <Head title="Nuevo Servicio" />

     <Heading icon='ri:user-add-line' title="Crear Usuario"/>

     <BookingForm/>
</template>