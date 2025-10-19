<!-- resources/js/Pages/Booking/partials/ChildForm.vue -->
<script setup lang="ts">
import { watch } from "vue"
import {
  FormField, FormItem, FormLabel, FormControl, FormMessage,
} from "@/components/ui/form"
import { Input } from "@/components/ui/input"
import {
  Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
} from "@/components/ui/select"
import { Button } from "@/components/ui/button"
import { CalendarIcon } from "lucide-vue-next"

import CalendarReka from "@/components/ui/CalendarReka.vue" // ← tu calendario (no modificado)
import { ChildFormService } from "@/services/childFormService"
import type { Child, ChildInput } from "@/types/Child"
import { getKinshipLabelByString } from "@/enums/kinkship.enum"

import type { DateValue } from "@internationalized/date"
import { DateFormatter, getLocalTimeZone, parseDate } from "@internationalized/date"

const props = defineProps<{
  child: Child | ChildInput
  kinkships: string[]
}>()

const emit = defineEmits<{
  (e: "saved", value: Child): void
  (e: "deleted", id: string): void
  (e: "loading", value: boolean): void
}>()

// Servicio (sin autosave) — se reinstancia al cambiar el registro
let service = new ChildFormService(props.child as Child)
function wire() {
  watch(() => service.loading.value, v => emit("loading", v), { immediate: true })
}
wire()

watch(
  () => (props.child as any)?.id,
  () => {
    service = new ChildFormService(props.child as Child)
    wire()
  }
)

const { isFieldDirty, loading, errores } = service

async function onSaveClick() {
  const ok = await service.guardar()
  if (ok) emit("saved", service.child.value)
}



// Utils fecha para label
const tz = getLocalTimeZone()
const df = new DateFormatter("es-MX", { dateStyle: "medium" })
const todayYMD = new Date().toISOString().slice(0, 10)
</script>

<template>
  <!-- Contenedor centrado y angosto -->
  <div class="max-w-md mx-auto space-y-4">
    
    <!-- Fila 2: Nacimiento | Parentesco -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Fila 1: Nombre -->
    <FormField v-slot="{ componentField }" name="name" :validate-on-blur="!isFieldDirty">
      <FormItem>
        <FormLabel>Nombre</FormLabel>
        <FormControl>
          <Input placeholder="Ej. Sofía" v-bind="componentField" />
        </FormControl>
        <FormMessage />
        <span v-if="errores['name']" class="text-sm text-destructive">
          {{ errores['name'][0] }}
        </span>
      </FormItem>
    </FormField>


      <!-- Parentesco -->
      <FormField v-slot="{ value, handleChange }" name="kinkship" :validate-on-blur="!isFieldDirty">
        <FormItem>
          <FormLabel>Parentesco</FormLabel>
          <FormControl>
            <Select :model-value="value" @update:model-value="handleChange">
              <SelectTrigger class="h-9 w-full">
                <SelectValue placeholder="Selecciona" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="k in kinkships" :key="k" :value="k">
                  {{ getKinshipLabelByString(k) }}
                </SelectItem>
              </SelectContent>
            </Select>
          </FormControl>
          <FormMessage />
          <span v-if="errores['kinkship']" class="text-sm text-destructive">
            {{ errores['kinkship'][0] }}
          </span>
        </FormItem>
      </FormField>
    </div>
          <!-- Nacimiento -->
      <FormField v-slot="{ value, handleChange }" name="birthdate" :validate-on-blur="!isFieldDirty">
        <FormItem>
          <FormLabel>Nacimiento</FormLabel>

          <!-- Etiqueta con icono y fecha humana -->
          <div class="flex items-center gap-2">
            <CalendarIcon class="h-4 w-4 text-muted-foreground" />
            <span class="text-sm text-muted-foreground">
              {{
                value
                  ? df.format((parseDate(value as string)).toDate(tz))
                  : "Selecciona fecha"
              }}
            </span>
          </div>

          <!-- TU calendario (no se toca) -->
          <div class="mt-2">
            <CalendarReka
              :model-value="(value as string) ? parseDate(value as string) : undefined"
              @update:modelValue="(dv?: DateValue) => handleChange(dv ? dv.toString() : '')"
              :max-value="parseDate(todayYMD)"
              weekday-format="short"
              class="w-full"
            />
          </div>

          <FormMessage />
          <span v-if="errores['birthdate']" class="text-sm text-destructive">
            {{ errores['birthdate'][0] }}
          </span>
        </FormItem>
      </FormField>

    <!-- Acciones -->
    <div class="mt-2 flex items-center justify-center gap-2">
      <Button size="sm" variant="outline" :disabled="loading" @click.prevent="onSaveClick">
        {{ service.child.value.id ? 'Actualizar' : 'Guardar' }}
      </Button>
       <span v-if="loading" class="text-xs text-muted-foreground">Guardando…</span>
    </div>

    <div class="text-center">
     
    </div>
  </div>
</template>
