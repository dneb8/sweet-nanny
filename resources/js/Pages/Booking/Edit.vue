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

// Tipos compartidos con el formulario
type AppointmentDTO = { start_date: string; end_date: string; duration: number }
type InitialBooking = {
  booking: {
    tutor_id: number
    address_id: number | null
    description: string
    recurrent: boolean
    child_ids: number[]
  }
  bookingAppointments: AppointmentDTO[]
  address: {
    postal_code: string
    street: string
    neighborhood: string
    type: string
    other_type: string
    internal_number: string
  }
}

const props = defineProps<{
  booking: Booking & {
    tutor: Tutor & { addresses?: Address[]; children?: Child[] }
    children: Child[]                     // niños asociados al booking
    address?: Address | null              // dirección seleccionada en el booking
    bookingAppointments: BookingAppointment[]
  }
  kinkships: string[]
  initialBooking: InitialBooking
}>()

// Asegurar que el tutor que pasamos al form tenga arrays definidos para addresses/children
const tutorForForm = computed(() => {
  const t = props.booking.tutor as Tutor & { addresses?: Address[]; children?: Child[] }
  const addresses = t.addresses ?? (props.booking.address ? [props.booking.address as Address] : [])
  const children = t.children ?? props.booking.children ?? []
  return { ...t, addresses, children }
})
</script>

<template>
  <Head title="Editar Servicio" />
  <Heading icon="fluent:calendar-edit-24-regular" title="Editar Servicio" />

  <BookingForm
    mode="edit"
    :tutor="tutorForForm"
    :kinkships="props.kinkships"
    :initial-booking="props.initialBooking"
    :booking-id="props.booking.id"
  />
</template>
