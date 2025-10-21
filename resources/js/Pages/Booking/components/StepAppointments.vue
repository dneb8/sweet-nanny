<script setup lang="ts">
import { ref, computed, watch } from "vue"
import { Label } from "@/components/ui/label"
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip"
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Button } from "@/components/ui/button"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import { Calendar } from "@/components/ui/calendar"
import { Input } from "@/components/ui/input"
import { Card, CardContent, CardTitle } from "@/components/ui/card"
import { CalendarIcon } from "lucide-vue-next"
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from "@/components/ui/select"
import TimePicker from "@/components/ui/Timepicker.vue"
import { useBoundField } from "@/services/bookingFormService"
import { DateFormatter, getLocalTimeZone, fromDate, today } from "@internationalized/date"
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from "@/components/ui/carousel"
import { Icon } from "@iconify/vue"

const recurrent = useBoundField<boolean>("booking.recurrent")
const appts = useBoundField<any[]>("appointments")

const tabValue = computed<string>({
  get: () => (recurrent.value.value ? "recurrente" : "fijo"),
  set: v => (recurrent.value.value = v === "recurrente")
})

type Row = { dateVal: any | null; time: string; duration: number }
const rows = ref<Row[]>([])

const tz = getLocalTimeZone()
const df = new DateFormatter("es-MX", { dateStyle: "short" })
// Only allow selecting dates from tomorrow onward
const minDate = today(tz).add({ days: 1 })
const maxCitas = 10
const toJsDate = (val: any): Date | null => val?.toDate?.(tz) ?? (val instanceof Date ? val : null)
function isoToRow(startISO?: string, endISO?: string): Row {
  if (!startISO || !endISO) return { dateVal: null, time: "08:00", duration: 1 }
  const start = new Date(startISO)
  const end = new Date(endISO)
  const durH = Math.max(1, Math.round((+end - +start) / 36e5))
  const dateVal = fromDate(new Date(start), tz)
  const time = start.toLocaleTimeString("es-MX", { hour: "2-digit", minute: "2-digit", hour12: false })
  return { dateVal, time, duration: durH }
}
function rowToIso(r: Row) {
  const base = toJsDate(r.dateVal)
  if (!base || !r.time || !r.duration) return { startISO: "", endISO: "" }
  const [h, m] = r.time.split(":").map(Number)
  const start = new Date(base); start.setHours(h, m, 0, 0)
  const end = new Date(start); end.setHours(end.getHours() + r.duration)
  return { startISO: start.toISOString(), endISO: end.toISOString() }
}
const isComplete = (r: Row) => !!(r.dateVal && r.time && r.duration > 0)

// Hydrate rows from appointments on load/edit
watch(
  () => appts.value.value,
  (list) => {
    if (!Array.isArray(list) || list.length === 0) return
    if (rows.value.length && rows.value.every(isComplete)) return
    rows.value = list.map(a => isoToRow(a?.start_date, a?.end_date))
  },
  { immediate: true, deep: true }
)

// Ensure at least one row exists in non-recurrent mode
watch(
  () => recurrent.value.value,
  (isRec) => {
    if (!isRec && rows.value.length === 0) {
      rows.value = [{ dateVal: null, time: "08:00", duration: 1 }]
    }
  },
  { immediate: true }
)

function syncRow(i: number) {
  const r = rows.value[i]
  if (!r) return
  let list = Array.isArray(appts.value.value) ? [...appts.value.value] : []

  if (!isComplete(r)) {
    if (list[i]) {
      list.splice(i, 1)
      rows.value.splice(i, 1)
    }
    appts.value.value = list
    return
  }

  const { startISO, endISO } = rowToIso(r)
  list[i] = {
    ...(list[i] || {}),
    start_date: startISO,
    end_date: endISO,
    duration: Math.max(1, Number(r.duration || 1)),
    status: list[i]?.status ?? "pending",
    payment_status: list[i]?.payment_status ?? "unpaid",
    extra_hours: list[i]?.extra_hours ?? 0,
    total_cost: list[i]?.total_cost ?? 0
  }
  if (!recurrent.value.value) {
    list = [list[0]]
    rows.value = [rows.value[0]]
  }
  appts.value.value = list
}

const canAdd = computed(
  () =>
    recurrent.value.value &&
    rows.value.length < maxCitas &&
    isComplete(rows.value[rows.value.length - 1])
)

function addRow() {
  if (!canAdd.value) return
  const last = rows.value[rows.value.length - 1]
  let nextDateVal: any = null
  const base = toJsDate(last?.dateVal)
  if (base) {
    const d = new Date(base); d.setDate(d.getDate() + 1)
    nextDateVal = fromDate(d, tz)
  }
  const time = last?.time || "08:00"
  const duration = last?.duration || 1
  rows.value.push({ dateVal: nextDateVal, time, duration })
  const list = Array.isArray(appts.value.value) ? [...appts.value.value] : []
  list.push(undefined as any)
  appts.value.value = list
}

function removeRow(i: number) {
  if (!recurrent.value.value || rows.value.length <= 1) return
  rows.value.splice(i, 1)
  const list = Array.isArray(appts.value.value) ? [...appts.value.value] : []
  list.splice(i, 1)
  appts.value.value = list
}

watch(
  () => recurrent.value.value,
  isRec => {
    if (!isRec) {
      rows.value = [rows.value[0] ?? { dateVal: null, time: "08:00", duration: 1 }]
      appts.value.value = appts.value.value?.length ? [appts.value.value[0]] : []
    } else if (rows.value.length === 0) {
      rows.value.push({ dateVal: null, time: "08:00", duration: 1 })
    }
  }
)
</script>

<template>
  <div class="space-y-6">
    <!-- Tabs -->
    <div class="space-y-3">
      <div class="flex items-center gap-2 max-w-3xl mx-auto">
        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger as-child>
              <span class="relative inline-flex cursor-help items-center justify-center">
                <Icon icon="ph:question-bold" class="relative z-10 size-4 text-gray-500 dark:text-gray-100" />
                <span class="pointer-events-none absolute -translate-x-1/5 bottom-[-2px] size-4 rounded-full animate-pulse bg-gradient-to-r from-rose-200 via-rose-300 to-rose-400 opacity-70 blur-[3px]" />
              </span>
            </TooltipTrigger>
            <TooltipContent class="max-w-xs text-xs">
              Un servicio fijo ocurre en una sola fecha y hora; uno recurrente puede tener varias fechas.
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
        <Label class="text-sm font-medium">Tipo</Label>
      </div>
      <Tabs v-model="tabValue">
        <TabsList class="grid w-full grid-cols-2 max-w-3xl mx-auto">
          <TabsTrigger value="fijo">Fijo</TabsTrigger>
          <TabsTrigger value="recurrente">Recurrente</TabsTrigger>
        </TabsList>
      </Tabs>
    </div>

    <!-- Citas en Carousel -->
    <div class="space-y-3">
      <Carousel class="relative w-full mx-auto max-w-3xl">
        <CarouselContent>
          <CarouselItem
            v-for="(r, i) in rows"
            :key="i"
            class="basis-full"
          >
            <div class="p-1 w-full">
              <Card class="p-3 sm:p-4 w-full bg-background/50">
                <CardContent class="p-0 w-full">
                  <CardTitle class="mb-4">Cita #{{ i + 1 }}</CardTitle>
                  <div class="grid gap-3 lg:grid-cols-4 md:grid-cols-2 grid-cols-1 items-end mb-8">
                    <!-- Fecha -->
                    <div class="w-full">
                      <div class="text-[11px] text-muted-foreground mb-1">Fecha</div>
                      <Popover>
                        <PopoverTrigger as-child>
                          <Button variant="outline" class="h-9 w-full justify-start">
                            <CalendarIcon class="mr-2 h-4 w-4" />
                            {{ r.dateVal ? df.format(((r.dateVal as any).toDate?.(tz)) || new Date()) : "Selecciona fecha" }}
                          </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0">
                          <Calendar
                            v-model="r.dateVal as any"
                            initial-focus
                            :min-value="minDate"
                            @update:modelValue="syncRow(i)"
                          />
                        </PopoverContent>
                      </Popover>
                    </div>

                    <!-- Duración -->
                    <div class="w-full">
                      <div class="text-[11px] text-muted-foreground mb-1">Duración</div>
                      <Select
                        :modelValue="String(r.duration)"
                        @update:modelValue="(v: string) => { r.duration = v != null ? Number(v) : 0; syncRow(i) }"
                      >
                        <SelectTrigger class="h-9 w-full">
                          <SelectValue placeholder="Selecciona duración" class="font-medium" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectGroup>
                            <SelectLabel>Horas</SelectLabel>
                            <SelectItem v-for="d in 8" :key="d" :value="String(d)">
                              {{ d }} h
                            </SelectItem>
                          </SelectGroup>
                        </SelectContent>
                      </Select>
                    </div>

                    <!-- Hora inicio -->
                    <div class="w-full">
                      <div class="text-[11px] text-muted-foreground mb-1">Hora inicio</div>
                      <TimePicker
                        :model-value="r.time"
                        placeholder="Hora"
                        @update:modelValue="(v: string) => { r.time = v; syncRow(i) }"
                      />
                    </div>

                    <!-- Termina -->
                    <div class="w-full relative">
                      <div class="text-[11px] text-muted-foreground mb-1">Hora fin</div>
                      <Input
                        :value="isComplete(r) ? new Date(rowToIso(r).endISO).toLocaleTimeString('es-MX',{hour:'2-digit',minute:'2-digit'}) : ''"
                        placeholder="—"
                        readonly
                        tabindex="-1"
                        class="h-9 bg-gray-100 text-foreground/80 pointer-events-none font-medium"
                      />
                      <p
                        v-if="!isComplete(r)"
                        class="absolute left-0 top-full mt-1 text-[11px] text-rose-600 pointer-events-none"
                      >
                        Completa fecha, hora y duración.
                      </p>
                    </div>
                  </div>

                  <!-- Acciones -->
                  <div class="mt-3 flex items-center justify-end">
                    <Button
                      v-if="recurrent.value.value && rows.length > 1"
                      variant="outline"
                      size="sm"
                      class="h-8"
                      @click="removeRow(i)"
                    >
                      Eliminar
                    </Button>
                  </div>
                </CardContent>
              </Card>
            </div>
          </CarouselItem>
        </CarouselContent>
        <CarouselPrevious v-if="rows.length > 1" />
        <CarouselNext
          v-if="rows.length > 1"
          type="button"
          class="
            after:content-['']
            after:absolute after:inset-0 after:-z-10 after:rounded-full
            after:bg-primary
            after:blur-xs after:animate-pulse
          "
        />
      </Carousel>

      <div class="flex items-center justify-between" v-if="recurrent.value.value">
        <p class="text-xs text-muted-foreground">{{ rows.length }}/{{ maxCitas }} citas</p>
        <Button
          variant="outline"
          size="sm"
          class="h-9"
          :disabled="!canAdd"
          @click="addRow"
        >
          Agregar otra cita
        </Button>
      </div>

      <p v-if="appts.errorMessage" class="text-xs text-rose-600">
        {{ appts.errorMessage }}
      </p>
    </div>
  </div>
</template>
