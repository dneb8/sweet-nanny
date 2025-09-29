<script setup lang="ts">
import { onMounted } from "vue"
import { Button } from "@/components/ui/button"
import { Stepper, StepperDescription, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from "@/components/ui/stepper"
import { Icon } from "@iconify/vue"

import StepBooking from "../components/StepBooking.vue"
import StepAppointments from "../components/StepAppointments.vue"
import StepAddress from "../components/StepAddress.vue"

import type { Address } from "@/types/Address"
import type { Child } from "@/types/Child"
import type { Tutor } from "@/types/Tutor"

import { useBookingForm, useBoundField } from "@/services/bookingFormService"

// Tipos de props
type AppointmentDTO = { start_date: string; end_date: string; duration: number }
type InitialBookingFromServer = {
  booking?: {
    tutor_id?: number
    address_id?: number | null
    description?: string
    recurrent?: boolean
    child_ids?: Array<number | string>
  }
  appointments?: AppointmentDTO[]
  bookingAppointments?: AppointmentDTO[]
  address?: {
    postal_code?: string
    street?: string
    neighborhood?: string
    type?: string
    other_type?: string
    internal_number?: string
  }
  // Cuando mandas el modelo plano $booking:
  tutor_id?: number
  description?: string
  recurrent?: boolean | number
  address_id?: number | null
  children?: Array<{ id: number }>
}

const props = defineProps<{
  tutor: Tutor & { addresses?: Address[]; children?: Child[] }
  kinkships: string[]
  initialBooking?: InitialBookingFromServer | null
  mode?: "edit" | "create"
  bookingId?: number
}>()

const {
  stepIndex, steps, nextStep, prevStep, onSubmit, isSubmitting, hydrateWithServerValues,
} = useBookingForm({
  mode: props.mode ?? "create",
  bookingId: props.bookingId,
})

if (props.initialBooking) {
  const safe = JSON.parse(JSON.stringify(props.initialBooking)) as InitialBookingFromServer
  hydrateWithServerValues(safe)
}

const tutorIdField = useBoundField<number>("booking.tutor_id")
onMounted(() => {
  tutorIdField.value.value = Number(props.tutor.id)
})
</script>

<template>
  <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
    <Stepper v-model="stepIndex" class="flex flex-col">
      <form @submit.prevent="onSubmit" class="flex flex-col gap-8">
        <div class="w-full max-w-6xl mx-auto">
          <div class="flex flex-wrap justify-center gap-4">
            <StepperItem
              v-for="step in steps"
              :key="step.step"
              v-slot="{ state }"
              class="relative flex flex-col items-center text-center flex-1 min-w-[80px]"
              :step="step.step"
            >
              <StepperSeparator
                v-if="step.step !== steps[steps.length - 1].step"
                class="absolute left-1/2 right-[-50%] top-5 h-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary hidden sm:block"
              />
              <StepperTrigger as-child>
                <Button
                  :variant="state === 'completed' || state === 'active' ? 'default' : 'outline'"
                  size="icon"
                  class="z-10 rounded-full shrink-0 transition"
                  :class="[state === 'active' && 'ring-2 ring-primary ring-offset-2']"
                >
                  <Icon :icon="step.icon" class="w-5 h-5" :class="[state === 'inactive' && 'opacity-60']" />
                </Button>
              </StepperTrigger>
              <StepperTitle :class="[state === 'active' && 'text-primary']" class="mt-3 text-sm font-medium">
                {{ step.title }}
              </StepperTitle>
              <StepperDescription class="text-xs text-muted-foreground hidden sm:block">
                {{ step.description }}
              </StepperDescription>
            </StepperItem>
          </div>
        </div>

        <div class="w-full">
          <div class="max-w-4xl mx-auto bg-muted/30 rounded-xl p-4 sm:p-6">
            <div v-show="stepIndex === 1">
              <StepBooking
                :tutor="props.tutor"
                :initial-children="props.tutor.children ?? []"
                :kinkships="props.kinkships"
              />
            </div>

            <div v-show="stepIndex === 2">
              <StepAppointments />
            </div>

            <div v-show="stepIndex === 3">
              <StepAddress :addresses="props.tutor.addresses ?? []" />
            </div>
          </div>
        </div>

        <div class="max-w-4xl mx-auto w-full flex justify-between items-center">
          <Button variant="outline" size="sm" @click.prevent="prevStep()" :disabled="stepIndex === 1">Atrás</Button>
          <div class="flex gap-2">
            <Button v-if="stepIndex !== steps.length" type="button" size="sm" @click="nextStep()">Siguiente</Button>
            <Button
              v-else
              size="sm"
              type="submit"
              :disabled="isSubmitting"
              class="bg-primary text-primary-foreground"
            >
              {{ props.initialBooking ? "Actualizar servicio" : "Crear servicio" }}
            </Button>
          </div>
        </div>
      </form>
    </Stepper>
  </div>
</template>
