<script setup lang="ts">
import type { Nanny } from '@/types/Nanny'

//Import de cards
import ProfileCard from './information/Profile.vue'
import AddressCard from './information/Address.vue'
import CoursesCard from './information/Course.vue'
import CareersCard from './information/Career.vue'
import QualitiesCard from './information/Quality.vue'
import ServicesCard from './information/Booking.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
nanny: Nanny
bookings: any 
}>()
</script>

<template>
<Head :title="`SweetNanny — Niñera ${props.nanny.user.name} ${props.nanny.user.surnames}`" />
  <div class="grid grid-cols-1 gap-5">
    <!-- Columna superior -->
    <div class="space-y-5">
      <ProfileCard :nanny="props.nanny" :key="props.nanny.id" />
      <div class="grid md:grid-cols-2 gap-5">
        <AddressCard :nanny="props.nanny" />
        <CoursesCard :nanny="props.nanny" />
        <CareersCard :nanny="props.nanny" />
        <QualitiesCard :nanny="props.nanny" />
      </div>
    </div>

    <!-- Servicios -->
    <div class="space-y-6">
      <ServicesCard :nanny="props.nanny" :bookings="props.bookings" />
    </div>
  </div>
</template>
