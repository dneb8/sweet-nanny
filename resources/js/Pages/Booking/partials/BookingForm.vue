<script setup lang="ts">
import { ref } from "vue"
import { Check, Circle, Dot } from "lucide-vue-next"
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { Stepper, StepperDescription, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from "@/components/ui/stepper"

// tipos correctos
import type { Booking } from '@/types/Booking'
import type { BookingAppointment } from '@/types/BookingAppointment'

// estado booking
const booking = ref<Booking>({
  id: 0,
  description: "",
  recurrent: false,
  created_at: "",
  tutor_id: 0,
  address_id: 0,
})

// estado appointments
const appointments = ref<BookingAppointment[]>([
  {
    id: 0,
    booking_id: 0,
    nanny_id: null,
    price_id: 0,
    start_date: "",
    end_date: "",
    status: "pending",
    payment_status: "unpaid",
    extra_hours: 0,
    total_cost: 0,
    created_at: "",
    updated_at: "",
  },
])

// stepper
const stepIndex = ref(1)
const steps = [
  { step: 1, title: "Datos del Booking", description: "Información básica" },
  { step: 2, title: "Citas", description: "Agendar appointments" },
]

const addAppointment = () => {
  appointments.value.push({
    id: 0,
    booking_id: 0,
    nanny_id: null,
    price_id: 0,
    start_date: "",
    end_date: "",
    status: "pending",
    payment_status: "unpaid",
    extra_hours: 0,
    total_cost: 0,
    created_at: "",
    updated_at: "",
  })
}

const onSubmit = () => {
  if (!booking.value.recurrent && appointments.value.length < 10) {
    alert("Debes registrar al menos 10 citas cuando no es recurrente.")
    return
  }

  console.log("Payload a enviar:", {
    booking: booking.value,
    appointments: appointments.value,
  })
}
</script>

<template>
  <Stepper v-slot="{ isNextDisabled, isPrevDisabled, nextStep, prevStep }" v-model="stepIndex" class="block w-full">
    <form
      @submit.prevent="() => {
        if (stepIndex === steps.length) {
          onSubmit()
        }
      }"
    >
      <!-- 🔹 Stepper Header -->
      <div class="flex w-full flex-start gap-2">
        <StepperItem
          v-for="step in steps"
          :key="step.step"
          v-slot="{ state }"
          class="relative flex w-full flex-col items-center justify-center"
          :step="step.step"
        >
          <StepperSeparator
            v-if="step.step !== steps[steps.length - 1].step"
            class="absolute left-[calc(50%+20px)] right-[calc(-50%+10px)] top-5 block h-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary"
          />
          <StepperTrigger as-child>
            <Button
              :variant="state === 'completed' || state === 'active' ? 'default' : 'outline'"
              size="icon"
              class="z-10 rounded-full shrink-0"
              :class="[state === 'active' && 'ring-2 ring-ring ring-offset-2 ring-offset-background']"
            >
              <Check v-if="state === 'completed'" class="size-5" />
              <Circle v-if="state === 'active'" />
              <Dot v-if="state === 'inactive'" />
            </Button>
          </StepperTrigger>
          <div class="mt-5 flex flex-col items-center text-center">
            <StepperTitle :class="[state === 'active' && 'text-primary']" class="text-sm font-semibold transition lg:text-base">
              {{ step.title }}
            </StepperTitle>
            <StepperDescription :class="[state === 'active' && 'text-primary']" class="sr-only text-xs text-muted-foreground transition md:not-sr-only lg:text-sm">
              {{ step.description }}
            </StepperDescription>
          </div>
        </StepperItem>
      </div>

      <!-- 🔹 Step Content -->
      <div class="flex flex-col gap-4 mt-4">
        <!-- Step 1 Booking -->
        <template v-if="stepIndex === 1">
          <div>
            <label>Descripción</label>
            <Input v-model="booking.description" type="text" placeholder="Detalle del booking" />
          </div>
          <div>
            <label>Tutor ID</label>
            <Input v-model="booking.tutor_id" type="number" min="1" />
          </div>
          <div>
            <label>Address ID</label>
            <Input v-model="booking.address_id" type="number" min="1" />
          </div>
          <div class="flex items-center gap-2">
            <input type="checkbox" v-model="booking.recurrent" id="recurrent" />
            <label for="recurrent">¿Es recurrente?</label>
          </div>
        </template>

        <!-- Step 2 BookingAppointments -->
        <template v-if="stepIndex === 2">
          <div v-if="booking.recurrent">
            <h3 class="font-semibold">Cita única</h3>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label>Fecha inicio</label>
                <Input v-model="appointments[0].start_date" type="datetime-local" />
              </div>
              <div>
                <label>Fecha fin</label>
                <Input v-model="appointments[0].end_date" type="datetime-local" />
              </div>
            </div>
          </div>

          <div v-else>
            <h3 class="font-semibold">Múltiples citas (mínimo 10)</h3>
            <div
              v-for="(appt, i) in appointments"
              :key="i"
              class="grid grid-cols-2 gap-4 mb-3 border p-2 rounded"
            >
              <div>
                <label>Fecha inicio</label>
                <Input v-model="appt.start_date" type="datetime-local" />
              </div>
              <div>
                <label>Fecha fin</label>
                <Input v-model="appt.end_date" type="datetime-local" />
              </div>
            </div>
            <Button size="sm" variant="outline" @click.prevent="addAppointment">
              + Agregar cita
            </Button>
            <p v-if="appointments.length < 10" class="text-red-500 text-sm mt-1">
              Debes registrar al menos 10 citas.
            </p>
          </div>
        </template>
      </div>

      <!-- 🔹 Buttons -->
      <div class="flex items-center justify-between mt-4">
        <Button :disabled="stepIndex === 1" variant="outline" size="sm" @click="prevStep()">
          Atrás
        </Button>
        <div class="flex items-center gap-3">
          <Button
            v-if="stepIndex !== steps.length"
            type="button"
            size="sm"
            @click="nextStep()"
          >
            Siguiente
          </Button>
          <Button
            v-if="stepIndex === steps.length"
            size="sm"
            type="submit"
            :disabled="!booking.recurrent && appointments.length < 10"
          >
            Crear booking
          </Button>
        </div>
      </div>
    </form>
  </Stepper>
</template>
