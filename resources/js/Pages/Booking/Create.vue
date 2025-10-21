<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import BookingForm from './partials/BookingForm.vue'
import Heading from '@/components/Heading.vue'

import type { Address } from '@/types/Address'
import type { Child } from '@/types/Child'
import type { Tutor } from '@/types/Tutor'

const props = defineProps<{
  tutor: Tutor & { addresses: Address[]; children: Child[] }
  kinkships: string[]
  qualities: Record<string, string>
  careers: Record<string, string>
  courseNames: Record<string, string>
}>()
</script>

<template>
  <Head title="Nuevo Servicio" />
  <Heading icon="fluent:calendar-add-24-regular" title="Crear Servicio" />
  <BookingForm 
    :tutor="props.tutor" 
    :kinkships="props.kinkships"
    :qualities="props.qualities"
    :careers="props.careers"
    :course-names="props.courseNames"
  />
</template>
