<script setup lang="ts">
import { Button } from "@/components/ui/button"
import {
  Stepper,
  StepperDescription,
  StepperItem,
  StepperSeparator,
  StepperTitle,
  StepperTrigger,
} from "@/components/ui/stepper"

import { Check, Circle, Dot } from "lucide-vue-next"

import StepBooking from "../components/StepBooking.vue"
import StepAppointments from "../components/StepAppointments.vue"
import StepAddress from "../components/StepAddress.vue"

import { useBookingForm } from "@/services/bookingFormService"

const {
  stepIndex, steps, nextStep, prevStep,
  onSubmit, isSubmitting,
} = useBookingForm()
</script>

<template>
  <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
    <Stepper v-model="stepIndex" class="flex flex-col">
      <form @submit.prevent="onSubmit" class="flex flex-col gap-8">
        <!-- HEADER: centrado y ancho (flex-wrap como tu ejemplo) -->
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
                class="absolute left-1/2 right-[-50%] top-5 h-0.5 shrink-0 rounded-full bg-muted
                      group-data-[state=completed]:bg-primary hidden sm:block"
              />
              <StepperTrigger as-child>
                <Button
                  :variant="state === 'completed' || state === 'active' ? 'default' : 'outline'"
                  size="icon"
                  class="z-10 rounded-full shrink-0 transition"
                  :class="[state === 'active' && 'ring-2 ring-primary ring-offset-2']"
                > 
                  <Icon
                    :icon="step.icon"
                    class="w-5 h-5 transition"
                    :class="[
                      state === 'inactive' && 'opacity-60',
                      state === 'active' && 'opacity-100',
                      state === 'completed' && 'opacity-100',
                    ]"
                  />
                </Button>
              </StepperTrigger>

              <StepperTitle
                :class="[state === 'active' && 'text-primary']"
                class="mt-3 text-sm font-medium"
              >
                {{ step.title }}
              </StepperTitle>
              <StepperDescription class="text-xs text-muted-foreground hidden sm:block">
                {{ step.description }}
              </StepperDescription>
            </StepperItem>
          </div>
        </div>

        <!-- CONTENT: centrado y un poco más angosto -->
        <div class="w-full">
          <div class="max-w-3xl mx-auto bg-muted/30 rounded-xl p-4 sm:p-6 shadow-inner">
            <StepBooking v-if="stepIndex === 1" />
            <StepAppointments v-if="stepIndex === 2" />
            <StepAddress v-if="stepIndex === 3" />
          </div>
        </div>

        <!-- BUTTONS -->
        <div class="max-w-4xl mx-auto w-full flex justify-between items-center">
          <Button
            variant="outline"
            size="sm"
            @click.prevent="prevStep()"
            :disabled="stepIndex === 1"
          >
            Atrás
          </Button>
          <div class="flex gap-2">
            <Button
              v-if="stepIndex !== steps.length"
              type="button"
              size="sm"
              @click="nextStep()"
            >
              Siguiente
            </Button>
            <Button
              v-else
              size="sm"
              type="submit"
              :disabled="isSubmitting"
              class="bg-primary text-primary-foreground"
            >
              Crear servicio
            </Button>
          </div>
        </div>
      </form>
    </Stepper>
  </div>
</template>
