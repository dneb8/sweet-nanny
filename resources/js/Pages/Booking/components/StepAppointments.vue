<script setup lang="ts">
import { ref, computed } from "vue"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { DateFormatter, getLocalTimeZone } from "@internationalized/date"
import { Calendar } from "@/components/ui/calendar"
import { Button } from "@/components/ui/button"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import { CalendarIcon } from "lucide-vue-next"
import { useBookingForm, useBoundField } from "@/services/bookingFormService"

const df = new DateFormatter("es-MX", { dateStyle: "long" })
const state = ref<{ date: any; time: string; duration: number }>({ date: null, time: "08:00", duration: 1 })

// campos "fantasma" para mostrar errores del arreglo appointments[0]
const appts = useBoundField<any[]>("appointments")

const { upsertSingleAppointment } = useBookingForm()

const endTimeHuman = computed(() => {
  if (!state.value.time) return ""
  const [h, m] = state.value.time.split(":").map(Number)
  const start = new Date()
  start.setHours(h, m, 0, 0)
  const end = new Date(start); end.setHours(end.getHours() + state.value.duration)
  return end.toLocaleTimeString("es-MX", { hour: "2-digit", minute: "2-digit" })
})

function commitAppointment() {
  if (!state.value.date) return
  const jsDate = state.value.date.toDate(getLocalTimeZone())
  const [h, m] = state.value.time.split(":").map(Number)
  jsDate.setHours(h, m, 0, 0)
  upsertSingleAppointment(jsDate.toISOString(), state.value.duration)
}
</script>

<template>
  <div class="grid gap-6 sm:grid-cols-2">
    <!-- Fecha -->
    <div class="sm:col-span-2">
      <Popover>
        <PopoverTrigger as-child>
          <Button variant="outline" class="w-full justify-start">
            <CalendarIcon class="mr-2 h-4 w-4" />
            {{ state.date ? df.format(state.date.toDate(getLocalTimeZone())) : "Selecciona una fecha" }}
          </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0">
          <Calendar v-model="state.date" initial-focus @update:modelValue="commitAppointment" />
        </PopoverContent>
      </Popover>
    </div>

    <!-- Hora inicio -->
    <div>
      <Label for="time">Hora de inicio</Label>
      <Input id="time" type="time" step="600" v-model="state.time" class="mt-2" @change="commitAppointment" />
    </div>

    <!-- Duración -->
    <div>
      <Label for="duration">Duración (máx. 8h)</Label>
      <select id="duration" v-model.number="state.duration" class="mt-2 w-full border rounded px-3 py-2"
        @change="commitAppointment">
        <option v-for="d in 8" :key="d" :value="d">{{ d }} horas</option>
      </select>
    </div>

    <!-- Hora fin + errores -->
    <div class="sm:col-span-2 text-sm text-muted-foreground">
      <p v-if="endTimeHuman">Termina aprox. a las <strong>{{ endTimeHuman }}</strong></p>
      <p v-if="appts.errorMessage" class="text-xs text-red-500 mt-1">{{ appts.errorMessage }}</p>
    </div>
  </div>
</template>
