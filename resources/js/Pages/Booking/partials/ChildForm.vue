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
import CalendarReka from "@/components/ui/CalendarReka.vue"
import { ChildFormService } from "@/services/childFormService"
import type { Child, ChildInput } from "@/types/Child"
import { getKinshipLabelByString } from "@/enums/kinkship.enum"
import type { DateValue } from "@internationalized/date"
import { DateFormatter, getLocalTimeZone, parseDate } from "@internationalized/date"

const props = defineProps<{ child: Child | ChildInput; kinkships: string[] }>()
const emit = defineEmits<{ (e: "saved", value: Child): void; (e: "deleted", id: string): void; (e: "loading", value: boolean): void }>()

let service = new ChildFormService(props.child as Child)
function wire() { watch(() => service.loading.value, v => emit("loading", v), { immediate: true }) }
wire()
watch(() => (props.child as any)?.id, () => { service = new ChildFormService(props.child as Child); wire() })

const { isFieldDirty, loading, errores } = service

async function onSaveClick() {
  const ok = await service.guardar()   
  if (ok) emit("saved", service.child.value)
}

const tz = getLocalTimeZone()
const df = new DateFormatter("es-MX", { dateStyle: "medium" })

// Rango permitido: de hoy-14 años a hoy
const now = new Date()
const todayYMD = new Date(now.getFullYear(), now.getMonth(), now.getDate()).toISOString().slice(0, 10)
const min14YMD = new Date(now.getFullYear() - 14, now.getMonth(), now.getDate()).toISOString().slice(0, 10)
</script>

<template>
  <!-- form local: evita submit del form padre -->
  <form class="max-w-md mx-auto space-y-4" @submit.prevent="onSaveClick">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <FormField v-slot="{ componentField }" name="name" :validate-on-blur="!isFieldDirty">
        <FormItem>
          <FormLabel>Nombre</FormLabel>
          <FormControl>
            <Input placeholder="Ej. Sofía" v-bind="componentField" @keydown.enter.stop />
          </FormControl>
          <FormMessage />
          <span v-if="errores['name']" class="text-sm text-destructive">{{ errores['name'][0] }}</span>
        </FormItem>
      </FormField>

      <FormField v-slot="{ value, handleChange }" name="kinkship" :validate-on-blur="!isFieldDirty">
        <FormItem>
          <FormLabel>Parentesco</FormLabel>
          <FormControl>
            <Select :model-value="value" @update:model-value="handleChange">
              <SelectTrigger class="h-9 w-full" type="button">
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
          <span v-if="errores['kinkship']" class="text-sm text-destructive">{{ errores['kinkship'][0] }}</span>
        </FormItem>
      </FormField>
    </div>

    <FormField v-slot="{ value, handleChange }" name="birthdate" :validate-on-blur="!isFieldDirty">
      <FormItem>
        <FormLabel>Nacimiento</FormLabel>
        <div class="flex items-center gap-2">
          <CalendarIcon class="h-4 w-4 text-muted-foreground" />
          <span class="text-sm text-muted-foreground">
            {{ value ? df.format((parseDate(value as string)).toDate(tz)) : "Selecciona fecha" }}
          </span>
        </div>
        <div class="mt-2">
          <CalendarReka
            :model-value="(value as string) ? parseDate(value as string) : undefined"
            @update:modelValue="(dv?: DateValue) => handleChange(dv ? dv.toString() : '')"
            :min-value="parseDate(min14YMD)"
            :max-value="parseDate(todayYMD)"
            weekday-format="short"
            class="w-full"
          />
        </div>
        <FormMessage />
        <span v-if="errores['birthdate']" class="text-sm text-destructive">{{ errores['birthdate'][0] }}</span>
      </FormItem>
    </FormField>

    <div class="mt-2 flex items-center justify-center gap-2">
      <Button size="sm" variant="outline" :disabled="loading" type="submit">
        {{ service.child.value.id ? 'Actualizar' : 'Guardar' }}
      </Button>
      <span v-if="loading" class="text-xs text-muted-foreground">Guardando…</span>
    </div>
  </form>
</template>
