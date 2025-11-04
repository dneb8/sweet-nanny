<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import GoogleMap from '@/components/GoogleMap.vue'
import type { Booking } from '@/types/Booking'
import type { BookingAppointment } from '@/types/BookingAppointment'
import { computed } from 'vue'
import { useBookingView } from '@/services/BookingService'

/* ==== Emits ==== */
const emit = defineEmits<{
  (e: 'openEditModal', section: 'dates' | 'children' | 'address'): void
  (e: 'routerGet', url: string): void
  (e: 'changeNanny'): void
}>()

/* ==== Helpers base ==== */
const MX_TZ = 'America/Mexico_City'
const TZ_REGEX = /[zZ]|[+\-]\d{2}:\d{2}$/

/** +N horas sin mutar el original */
function addHours(date: Date, hours: number): Date {
  const d = new Date(date.getTime())
  d.setHours(d.getHours() + hours)
  return d
}

/** ¿Trae Z/offset explícito? */
function hasExplicitTZ(iso: string): boolean {
  return TZ_REGEX.test(iso ?? '')
}

/** Normaliza espacio a 'T' (no cambia zona) */
function normalizeIsoBasic(iso: string): string {
  return iso?.includes(' ') ? iso.replace(' ', 'T') : iso
}

/** Parse “naive” como local; si trae TZ, usa Date normal. */
function parseDateFlexible(iso: string): Date {
  if (!iso) return new Date(NaN)
  const s = normalizeIsoBasic(iso)

  if (hasExplicitTZ(s)) return new Date(s)

  const m = s.match(/^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})(?::(\d{2}))?/)
  if (!m) return new Date(s)

  const [, Y, MM, DD, hh, mm, ss] = m
  return new Date(
    Number(Y),
    Number(MM) - 1,
    Number(DD),
    Number(hh),
    Number(mm),
    Number(ss ?? 0),
    0
  )
}

/* ==== Formateadores con compensación +6 h ==== */
/** Siempre suma +6 h para contrarrestar el corrimiento que ves en UI. */
function fmtTimeMX12_plus6(iso: string): string {
  const parsed = parseDateFlexible(iso)
  const shifted = addHours(parsed, 6)
  return new Intl.DateTimeFormat('es-MX', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true,
    timeZone: MX_TZ,
  }).format(shifted)
}

function fmtDateTimeMX12_plus6(iso: string): string {
  const parsed = parseDateFlexible(iso)
  const shifted = addHours(parsed, 6)
  const fd = new Intl.DateTimeFormat('es-MX', {
    dateStyle: 'medium',
    timeZone: MX_TZ,
  }).format(shifted)
  const ft = new Intl.DateTimeFormat('es-MX', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true,
    timeZone: MX_TZ,
  }).format(shifted)
  return `${fd} · ${ft}`
}

/* ==== Props ==== */
const props = defineProps<{
  booking: Booking
  appointment: BookingAppointment
  canEditAppointment: boolean
  canChooseNanny?: boolean
  canChangeNanny?: boolean
  getEditDisabledReason?: (a: BookingAppointment) => string
  fmtReadableDateTime?: (iso: string) => string
  fmtTimeTZ?: (iso: string) => string
}>()

const v = useBookingView(props.booking)

/* ==== Wrappers (usan +6 h por defecto) ==== */
const safeGetDisabledReason = (a: BookingAppointment) =>
  (props.getEditDisabledReason ? props.getEditDisabledReason(a) : '') || ''

const safeFmtReadable = (iso: string) =>
  (props.fmtReadableDateTime ? props.fmtReadableDateTime(iso) : fmtDateTimeMX12_plus6(iso))

const safeFmtTime = (iso: string) =>
  (props.fmtTimeTZ ? props.fmtTimeTZ(iso) : fmtTimeMX12_plus6(iso))

/* ==== Derivados ==== */
const appointmentChildren = computed(() => props.appointment?.children ?? [])
const appointmentAddress  = computed(() => props.appointment?.addresses?.[0] ?? null)
</script>

<template>
  <div class="grid gap-4 md:grid-cols-12">
    <!-- LEFT -->
    <div class="md:col-span-8 space-y-4">
      <div class="rounded-3xl border border-white/30 bg-white/20 dark:border-white/10 dark:bg-white/5 backdrop-blur-xl shadow-lg p-4 sm:p-5">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold mb-1">
              {{ safeFmtReadable(props.appointment.start_date) }}
            </h3>
            <div class="flex items-center gap-2">
              <Badge :class="v.statusBadge(props.booking.status)" class="px-2 py-0.5 text-[11px]">
                {{ props.appointment.status }}
              </Badge>
            </div>
          </div>

          <div v-if="props.canEditAppointment">
            <Button
              variant="ghost"
              size="sm"
              @click="emit('openEditModal','dates')"
              :title="safeGetDisabledReason(props.appointment) || 'Editar fechas'"
            >
              <Icon icon="lucide:calendar-clock" class="h-4 w-4 mr-2 opacity-80" />
              <span class="opacity-90">Editar fechas</span>
            </Button>
          </div>
          <div v-else-if="safeGetDisabledReason(props.appointment)" class="text-xs text-muted-foreground">
            {{ safeGetDisabledReason(props.appointment) }}
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <p class="text-[11px] text-muted-foreground mb-1">Hora inicio</p>
            <p class="font-medium flex items-center gap-1">
              <Icon icon="lucide:clock" class="h-4 w-4 opacity-70" />
              {{ safeFmtTime(props.appointment.start_date) }}
            </p>
          </div>
          <div>
            <p class="text-[11px] text-muted-foreground mb-1">Hora fin</p>
            <p class="font-medium flex items-center gap-1">
              <Icon icon="lucide:clock" class="h-4 w-4 opacity-70" />
              {{ safeFmtTime(props.appointment.end_date) }}
            </p>
          </div>
        </div>

        <!-- <div v-if="props.appointment.total_cost" class="mb-4">
          <p class="text-[11px] text-muted-foreground mb-1">Costo total</p>
          <p class="text-xl font-semibold">${{ props.appointment.total_cost }}</p>
        </div> -->

        <!-- Niñera -->
        <div class="pt-4 border-t border-white/20">
          <p class="text-[11px] text-muted-foreground mb-2">Niñera asignada</p>
          
          <!-- Nanny assigned -->
          <div v-if="props.appointment.nanny">
            <div class="flex items-center gap-3 p-3 rounded-xl bg-white/40 dark:bg-white/10">
              <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                <Icon icon="lucide:user" class="h-5 w-5 text-primary" />
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium">
                  {{ props.appointment.nanny.user.name }} {{ props.appointment.nanny.user.surnames }}
                </p>
                <p class="text-[12px] text-muted-foreground">{{ props.appointment.nanny.user.email }}</p>
              </div>
            </div>
            
            <!-- Change Nanny button -->
            <div v-if="props.canChangeNanny" class="mt-3 text-center">
              <Button
                variant="outline"
                size="sm"
                class="w-full sm:w-auto"
                @click="emit('changeNanny')"
              >
                <Icon icon="lucide:repeat" class="mr-2 h-4 w-4" />
                Cambiar niñera
              </Button>
            </div>
          </div>
          
          <!-- No nanny assigned -->
          <div v-else>
            <div v-if="props.canChooseNanny" class="text-center py-4">
              <p class="text-sm text-muted-foreground mb-3">No hay niñera asignada</p>
              <Button
                variant="default"
                class="w-full sm:w-auto"
                @click="emit('routerGet', route('bookings.appointments.nannies.choose', { booking: props.booking.id, appointment: props.appointment.id }))"
              >
                <Icon icon="lucide:user-plus" class="mr-2 h-4 w-4" />
                Elegir niñera
              </Button>
            </div>
            <div v-else class="text-sm text-muted-foreground py-2">No hay niñera asignada</div>
          </div>
        </div>

        <!-- Niños -->
        <div class="mt-4 pt-4 border-t border-white/20">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold flex items-center gap-2">
              <Icon icon="lucide:baby" class="h-4 w-4" /> Niños ({{ appointmentChildren.length }})
            </h3>
            <Button
              v-if="props.canEditAppointment"
              variant="ghost"
              size="sm"
              @click="emit('openEditModal','children')"
              class="h-7 text-xs"
            >
              <Icon icon="lucide:edit-3" class="h-3.5 w-3.5 mr-1" />
              Editar
            </Button>
          </div>

          <div v-if="appointmentChildren.length === 0" class="text-[13px] text-muted-foreground">
            No hay niños asignados
          </div>

          <div v-else class="flex flex-col gap-2">
            <div
              v-for="child in appointmentChildren"
              :key="child.id"
              class="flex items-center gap-2 rounded-xl border border-white/20 bg-white/40 dark:bg-white/10 backdrop-blur-md px-3 py-2"
            >
              <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 shrink-0">
                <Icon icon="lucide:user-round" class="h-4 w-4 text-primary" />
              </span>
              <div class="leading-tight min-w-0">
                <p class="text-sm font-medium truncate">
                  {{ child.name }}
                  <Badge v-if="child.deleted_at" variant="outline" class="ml-1 px-1.5 py-0.5 text-[10px]">Eliminado</Badge>
                </p>
                <p class="text-[12px] text-muted-foreground">
                  {{ child.birthdate ? new Date(child.birthdate).toLocaleDateString('es-MX') : '—' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Acciones -->
        <div class="flex gap-2 mt-4 pt-4 border-t border-white/20">
          <Button
            v-if="props.appointment.status !== 'cancelled'"
            variant="destructive"
            size="sm"
            class="flex-1"
            @click="emit('routerGet', route('bookings.appointments.cancel', { booking: props.booking.id, appointment: props.appointment.id }))"
          >
            <Icon icon="lucide:x-circle" class="mr-2 h-4 w-4" />
            Cancelar cita
          </Button>
          <div v-else class="flex-1 flex items-center justify-center gap-2 text-sm text-muted-foreground py-2">
            <Icon icon="lucide:ban" class="h-4 w-4" />
            <span>Cita cancelada</span>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT: Dirección + Mapa -->
    <aside class="md:col-span-4 space-y-4">
      <div class="rounded-3xl border border-white/30 bg-white/25 dark:border-white/10 dark:bg-white/5 backdrop-blur-xl shadow-lg p-4 sm:p-5">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-sm font-semibold flex items-center gap-2">
            <Icon icon="lucide:map-pin" class="h-4 w-4" /> Dirección
          </h3>
          <Button
            v-if="props.canEditAppointment"
            variant="ghost"
            size="sm"
            @click="emit('openEditModal','address')"
            class="h-7 text-xs"
          >
            <Icon icon="lucide:edit-3" class="h-3.5 w-3.5 mr-1" />
            Editar
          </Button>
        </div>

        <div v-if="!appointmentAddress" class="text-[13px] text-muted-foreground">No especificada</div>
        <div v-else class="space-y-3 text-[13px]">
          <div class="space-y-1.5">
            <p class="font-medium">
                {{ appointmentAddress.name }}
              <span v-if="appointmentAddress.internal_number">, Int. {{ appointmentAddress.internal_number }}</span>
              
            </p>
            <p class="text-muted-foreground">{{ appointmentAddress.street }} {{ appointmentAddress.external_number }},{{ appointmentAddress.neighborhood }}</p>
            <p class="text-muted-foreground">CP: {{ appointmentAddress.postal_code }}</p>
            <Badge v-if="appointmentAddress.type" variant="secondary" class="mt-1 px-2 py-0.5 text-[11px]">
              {{ appointmentAddress.type }}
            </Badge>
          </div>

          <GoogleMap
            :latitude="+(appointmentAddress?.latitude ?? 19.704)"
            :longitude="+(appointmentAddress?.longitude ?? -103.344)"
            :zoom="16"
            height="300px"
            :showMarker="true"
          />
        </div>
      </div>
    </aside>
  </div>
</template>
