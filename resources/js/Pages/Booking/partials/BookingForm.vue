<script setup lang="ts">
import { Check, Circle, Dot } from "lucide-vue-next"
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { Stepper, StepperDescription, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from "@/components/ui/stepper"

// 🔹 Importa la lógica desde el service
import { useBookingForm } from "@/services/bookingFormService"

const { formSchema, stepIndex, steps, onSubmit, toTypedSchema } = useBookingForm()
</script>

<template>
  <Form
    v-slot="{ meta, values, validate }"
    as=""
    keep-values
    :validation-schema="toTypedSchema(formSchema[stepIndex - 1])"
  >
    <Stepper
      v-slot="{ isNextDisabled, isPrevDisabled, nextStep, prevStep }"
      v-model="stepIndex"
      class="block w-full"
    >
      <form
        @submit="(e) => {
          e.preventDefault()
          validate()
          if (stepIndex === steps.length && meta.valid) {
            onSubmit(values)
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
                :disabled="state !== 'completed' && !meta.valid"
              >
                <Check v-if="state === 'completed'" class="size-5" />
                <Circle v-if="state === 'active'" />
                <Dot v-if="state === 'inactive'" />
              </Button>
            </StepperTrigger>
            <div class="mt-5 flex flex-col items-center text-center">
              <StepperTitle
                :class="[state === 'active' && 'text-primary']"
                class="text-sm font-semibold transition lg:text-base"
              >
                {{ step.title }}
              </StepperTitle>
              <StepperDescription
                :class="[state === 'active' && 'text-primary']"
                class="sr-only text-xs text-muted-foreground transition md:not-sr-only lg:text-sm"
              >
                {{ step.description }}
              </StepperDescription>
            </div>
          </StepperItem>
        </div>

        <!-- 🔹 Step Content -->
        <div class="flex flex-col gap-4 mt-4">
          <!-- Step 1 -->
          <template v-if="stepIndex === 1">
            <FormField v-slot="{ componentField }" name="title">
              <FormItem>
                <FormLabel>Título del servicio</FormLabel>
                <FormControl>
                  <Input type="text" placeholder="Ej. Cuidado por la tarde" v-bind="componentField" />
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>
            <FormField v-slot="{ componentField }" name="description">
              <FormItem>
                <FormLabel>Descripción</FormLabel>
                <FormControl>
                  <Input type="text" placeholder="Breve detalle del servicio" v-bind="componentField" />
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>
          </template>

          <!-- Step 2 -->
          <template v-if="stepIndex === 2">
            <FormField v-slot="{ componentField }" name="date">
              <FormItem>
                <FormLabel>Fecha</FormLabel>
                <FormControl>
                  <Input type="date" v-bind="componentField" />
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>
            <FormField v-slot="{ componentField }" name="time">
              <FormItem>
                <FormLabel>Hora</FormLabel>
                <FormControl>
                  <Input type="time" v-bind="componentField" />
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>
            <FormField v-slot="{ componentField }" name="duration">
              <FormItem>
                <FormLabel>Duración (hrs)</FormLabel>
                <FormControl>
                  <Input type="number" min="1" v-bind="componentField" />
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>
            <FormField v-slot="{ componentField }" name="address">
              <FormItem>
                <FormLabel>Dirección</FormLabel>
                <FormControl>
                  <Input type="text" placeholder="Ej. Calle 123, Ciudad" v-bind="componentField" />
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>
          </template>

          <!-- Step 3 -->
          <template v-if="stepIndex === 3">
            <FormField v-slot="{ componentField }" name="serviceType">
              <FormItem>
                <FormLabel>Tipo de servicio</FormLabel>
                <Select v-bind="componentField">
                  <FormControl>
                    <SelectTrigger>
                      <SelectValue placeholder="Selecciona un tipo" />
                    </SelectTrigger>
                  </FormControl>
                  <SelectContent>
                    <SelectGroup>
                      <SelectItem value="por_horas">Por horas</SelectItem>
                      <SelectItem value="tiempo_completo">Tiempo completo</SelectItem>
                      <SelectItem value="tutoria">Tutoría</SelectItem>
                    </SelectGroup>
                  </SelectContent>
                </Select>
                <FormMessage />
              </FormItem>
            </FormField>
            <FormField v-slot="{ componentField }" name="requirements">
              <FormItem>
                <FormLabel>Requerimientos especiales</FormLabel>
                <FormControl>
                  <Input
                    type="text"
                    placeholder="Opcional (ej. inglés, experiencia...)"
                    v-bind="componentField"
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>
          </template>
        </div>

        <!-- 🔹 Buttons -->
        <div class="flex items-center justify-between mt-4">
          <Button :disabled="isPrevDisabled" variant="outline" size="sm" @click="prevStep()">
            Atrás
          </Button>
          <div class="flex items-center gap-3">
            <Button
              v-if="stepIndex !== steps.length"
              :type="meta.valid ? 'button' : 'submit'"
              :disabled="isNextDisabled"
              size="sm"
              @click="meta.valid && nextStep()"
            >
              Siguiente
            </Button>
            <Button v-if="stepIndex === steps.length" size="sm" type="submit">
              Crear servicio
            </Button>
          </div>
        </div>
      </form>
    </Stepper>
  </Form>
</template>
