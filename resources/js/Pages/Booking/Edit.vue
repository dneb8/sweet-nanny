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
    children: Child[]
    address?: Address | null
    bookingAppointments: BookingAppointment[]
  }
  kinkships: string[]
  qualities: Record<string, string>
  degrees: Record<string, string>
  courseNames: Record<string, string>
  initialBooking: any
}>()

// Tutor con fallback de children/address desde el booking
const tutorForForm = computed(() => {
  const t = props.booking.tutor as Tutor & { addresses?: Address[]; children?: Child[] }
  const addresses = t.addresses ?? (props.booking.address ? [props.booking.address as Address] : [])
  const children  = t.children  ?? props.booking.children ?? []
  return { ...t, addresses, children }
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
    :degrees="props.degrees"
    :course-names="props.courseNames"
    :initial-booking="props.initialBooking"
  />
</template>
