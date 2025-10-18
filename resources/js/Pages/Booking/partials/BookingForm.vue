<!-- resources/js/Pages/Bookings/BookingForm.vue -->
<script setup lang="ts">
import { ref, onMounted, watch, nextTick } from "vue"
import { Button } from "@/components/ui/button"
import { Stepper, StepperDescription, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from "@/components/ui/stepper"
import { Icon } from "@iconify/vue"
import StepBooking from "../components/StepBooking.vue"
import StepAppointments from "../components/StepAppointments.vue"
import StepAddress from "../components/StepAddress.vue"
import StepQualitiesCourses from "../components/StepQualitiesCourses.vue"
import type { Address } from "@/types/Address"
import type { Child } from "@/types/Child"
import type { Tutor } from "@/types/Tutor"
import type { Booking } from "@/types/Booking"
import { BookingFormService, useBoundField } from "@/services/bookingFormService"

const props = withDefaults(defineProps<{
  tutor: (Tutor & { addresses?: Address[]; children?: Child[] }) | null
  kinkships: string[]
  qualities?: Record<string, string>
  degrees?: Record<string, string>
  courseNames?: Record<string, string>
  initialBooking?: Partial<Booking> | null
  mode?: "edit" | "create"
  bookingId?: number
}>(), {
  initialBooking: null,
  mode: "create",
  qualities: () => ({}),
  degrees: () => ({}),
  courseNames: () => ({}),
})

// Instancia del service (pasamos tutor_id desde el front)
const formService = new BookingFormService(
  props.initialBooking as Booking | undefined,
  props.tutor?.id
)

// Registrar tutor_id como campo del form (hidden)
const tutorIdBF = useBoundField<number>("booking.tutor_id")
onMounted(() => {
  if (props.tutor?.id) tutorIdBF.value.value = Number(props.tutor.id)
})
watch(() => props.tutor?.id, (id) => {
  tutorIdBF.value.value = id ? Number(id) : 0
})

const stepIndex = ref(1)
const steps = [
  { step: 1, title: "Servicio",  description: "Describe tu servicio",        icon: "solar:clipboard-text-broken" },
  { step: 2, title: "Citas",     description: "Selecciona fecha y hora",     icon: "solar:calendar-linear" },
  { step: 3, title: "Dirección", description: "Lugar del servicio",          icon: "solar:map-point-linear" },
  { step: 4, title: "Requisitos", description: "Cualidades y formación",     icon: "solar:user-check-linear" },
]

// Escucha un "tick" que el service incrementa cuando detecta errores
watch(() => formService.errorTick.value, async () => {
  const s = formService.lastInvalidStep.value
  if (!s) return
  stepIndex.value = s
  await nextTick()
  // Focus heurístico del primer campo con error
  const key = formService.lastInvalidField.value || ""
  // Remueve índices de array al final (appointments.0.start_date -> appointments.start_date)
  const baseKey = key.replace(/\.\d+(?=\.|$)/g, "")
  const last = baseKey.split(".").pop() || ""
  const candidates: (string | null)[] = [
    // IDs frecuentes en tus inputs
    last,
    // Mapeos comunes
    baseKey === "booking.description" ? "booking-description" : null,
    baseKey === "address.postal_code" ? "postal_code" : null,
  ].filter(Boolean) as string[]
  let el: Element | null = null
  for (const id of candidates) {
    if (id !== null) {
      el = document.getElementById(id)
      if (el) break
    }
  }
  if (!el && key) {
    // Intenta por atributo name exacto con notación bracket
    const name = key.replace(/\.(\d+)(?=\.|$)/g, "[$1]").replace(/\./g, "][") + "]"
    el = document.querySelector(`[name="${name.startsWith("]") ? name.slice(1) : name}"]`)
  }
  if (!el) el = document.querySelector('[aria-invalid="true"]')
  if (el instanceof HTMLElement) el.focus()
})

function nextStep() { if (stepIndex.value < steps.length) stepIndex.value += 1 }
function prevStep() { if (stepIndex.value > 1) stepIndex.value -= 1 }

// Al enviar, el service se encarga de validar y, si hay errores, disparará errorTick
const submit = async () => {
  if (props.mode === "edit" && props.bookingId) await formService.updateBooking()
  else await formService.saveBooking()
}
</script>

<template>
  <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
    <Stepper v-model="stepIndex" class="flex flex-col">
      <form @submit.prevent="submit" class="flex flex-col gap-8">
        <input type="hidden" name="booking[tutor_id]" :value="tutorIdBF.value ?? ''" />

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
                :initial-children="props.tutor?.children ?? []"
                :kinkships="props.kinkships"
              />
            </div>

            <div v-show="stepIndex === 2">
              <StepAppointments />
            </div>

            <div v-show="stepIndex === 3">
              <StepAddress :addresses="props.tutor?.addresses ?? []" />
            </div>

            <div v-show="stepIndex === 4">
              <StepQualitiesCourses 
                :qualities="props.qualities"
                :degrees="props.degrees"
                :course-names="props.courseNames"
              />
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
              :disabled="!formService.canSubmit"
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
