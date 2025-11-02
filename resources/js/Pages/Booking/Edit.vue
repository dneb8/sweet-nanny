<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import BookingForm from './partials/BookingForm.vue'
import Heading from '@/components/Heading.vue'

import type { Address } from '@/types/Address'
import type { Child } from '@/types/Child'
import type { Tutor } from '@/types/Tutor'
import type { Booking } from '@/types/Booking'
import type { BookingAppointment } from '@/types/BookingAppointment'

const props = defineProps<{
  booking: Booking & {
    tutor: Tutor & { addresses?: Address[]; children?: Child[] }
    bookingAppointments: BookingAppointment[]
  }
  kinkships: string[]
  qualities: Record<string, string>
  careers: Record<string, string>
  courseNames: Record<string, string>
  initialBooking: any
}>()

// Tutor with fallback from tutor's own addresses/children (not from booking)
const tutorForForm = computed(() => {
  const t = props.booking.tutor as Tutor & { addresses?: Address[]; children?: Child[] }
  // Note: address and children now live on BookingAppointment, not Booking
  // The tutor already has their own addresses and children loaded
  return { ...t, addresses: t.addresses ?? [], children: t.children ?? [] }
})
</script>

<template>
  <Head title="Editar Servicio" />
  <Heading icon="fluent:calendar-edit-24-regular" title="Editar Servicio" />

  <BookingForm
    mode="edit"
    :booking-id="props.booking.id"
    :tutor="tutorForForm"
    :kinkships="props.kinkships"
    :qualities="props.qualities"
    :careers="props.careers"
    :course-names="props.courseNames"
    :initial-booking="props.initialBooking"
  />
</template>
