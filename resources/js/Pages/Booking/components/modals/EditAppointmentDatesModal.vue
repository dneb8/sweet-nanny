<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useForm as useInertiaForm } from '@inertiajs/vue3'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { Calendar } from '@/components/ui/calendar'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select'
import TimePicker from '@/components/ui/Timepicker.vue'
import { CalendarIcon } from 'lucide-vue-next'
import { DateFormatter, getLocalTimeZone, fromDate, today } from '@internationalized/date'
import type { BookingAppointment } from '@/types/BookingAppointment'
import type { Booking } from '@/types/Booking'

const props = defineProps<{
    appointment: BookingAppointment
    booking: Booking
}>()

const emit = defineEmits<{
    (e: 'close'): void
    (e: 'saved'): void
}>()

const tz = getLocalTimeZone()
const df = new DateFormatter('es-MX', { dateStyle: 'short' })
const minDate = today(tz).add({ days: 1 })

// Parse the existing start_date and end_date
function parseToLocalDateTime(s: string): Date {
    const match = s?.match?.(/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2})(?::\d{2})?(?:\.\d+)?(?:Z|[+\-]\d{2}:\d{2})?$/)
    const base = match ? match[1] + ':00' : s
    const d = new Date(base)
    return new Date(d.getFullYear(), d.getMonth(), d.getDate(), d.getHours(), d.getMinutes(), 0, 0)
}

function toLocalISO(d: Date): string {
    const pad = (n: number) => String(n).padStart(2, '0')
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}:00`
}

const start = parseToLocalDateTime(props.appointment.start_date)
const end = parseToLocalDateTime(props.appointment.end_date)
const durH = Math.max(1, Math.round((+end - +start) / 36e5))

const dateVal = ref<any>(fromDate(new Date(start), tz))
const time = ref(`${String(start.getHours()).padStart(2, '0')}:${String(start.getMinutes()).padStart(2, '0')}`)
const duration = ref(durH)

const toJsDate = (val: any): Date | null => val?.toDate?.(tz) ?? (val instanceof Date ? val : null)

const endDate = computed(() => {
    const base = toJsDate(dateVal.value)
    if (!base || !time.value || !duration.value) return null
    const [h, m] = time.value.split(':').map(Number)
    const startDate = new Date(base)
    startDate.setHours(h, m, 0, 0)
    const endDate = new Date(startDate)
    endDate.setHours(endDate.getHours() + duration.value)
    return endDate
})

const endDateISO = computed(() => {
    if (!endDate.value) return ''
    return toLocalISO(endDate.value)
})

const startDateISO = computed(() => {
    const base = toJsDate(dateVal.value)
    if (!base || !time.value) return ''
    const [h, m] = time.value.split(':').map(Number)
    const startDate = new Date(base)
    startDate.setHours(h, m, 0, 0)
    return toLocalISO(startDate)
})

const isComplete = computed(() => !!(dateVal.value && time.value && duration.value > 0))

const form = useInertiaForm({
    start_date: startDateISO.value,
    end_date: endDateISO.value,
    duration: duration.value,
})

// Update form when values change
watch([startDateISO, endDateISO, duration], () => {
    form.start_date = startDateISO.value
    form.end_date = endDateISO.value
    form.duration = duration.value
})

function submit() {
    form.patch(route('bookings.appointments.update-dates', { booking: props.booking.id, appointment: props.appointment.id }), {
        onSuccess: () => {
            emit('saved')
            emit('close')
        },
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="space-y-4">
        <div class="grid gap-3 grid-cols-1 md:grid-cols-2 items-end">
            <!-- Fecha -->
            <div class="w-full">
                <Label class="text-sm font-medium mb-2">Fecha</Label>
                <Popover>
                    <PopoverTrigger as-child>
                        <Button variant="outline" class="h-9 w-full justify-start">
                            <CalendarIcon class="mr-2 h-4 w-4" />
                            {{ dateVal ? df.format(dateVal.toDate?.(tz) || new Date()) : 'Selecciona fecha' }}
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-auto p-0">
                        <Calendar v-model="dateVal" initial-focus :min-value="minDate" />
                    </PopoverContent>
                </Popover>
            </div>

            <!-- Hora inicio -->
            <div class="w-full">
                <Label class="text-sm font-medium mb-2">Hora inicio</Label>
                <TimePicker v-model="time" placeholder="Hora" />
            </div>

            <!-- Duración -->
            <div class="w-full">
                <Label class="text-sm font-medium mb-2">Duración</Label>
                <Select :model-value="String(duration)" @update:model-value="(v: string) => (duration = Number(v))">
                    <SelectTrigger class="h-9 w-full">
                        <SelectValue placeholder="Selecciona duración" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectLabel>Horas</SelectLabel>
                            <SelectItem v-for="d in 8" :key="d" :value="String(d)">{{ d }} h</SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>

            <!-- Hora fin (calculada) -->
            <div class="w-full">
                <Label class="text-sm font-medium mb-2">Hora fin</Label>
                <Input
                    :value="
                        endDate
                            ? new Date(endDate).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })
                            : ''
                    "
                    placeholder="—"
                    readonly
                    tabindex="-1"
                    class="h-9 bg-zinc-100 dark:bg-zinc-800 text-foreground/80 pointer-events-none font-medium"
                />
            </div>
        </div>

        <p v-if="!isComplete" class="text-xs text-rose-600">Completa fecha, hora y duración.</p>
        <p v-if="form.errors.start_date" class="text-xs text-rose-600">{{ form.errors.start_date }}</p>
        <p v-if="form.errors.end_date" class="text-xs text-rose-600">{{ form.errors.end_date }}</p>
        <p v-if="form.errors.duration" class="text-xs text-rose-600">{{ form.errors.duration }}</p>

        <div class="flex justify-end gap-2 pt-4">
            <Button variant="outline" @click="emit('close')" :disabled="form.processing">Cancelar</Button>
            <Button @click="submit" :disabled="!isComplete || form.processing">
                {{ form.processing ? 'Guardando...' : 'Guardar' }}
            </Button>
        </div>
    </div>
</template>
