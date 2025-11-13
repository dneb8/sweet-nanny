<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import GoogleMap from '@/components/GoogleMap.vue'
import type { Booking } from '@/types/Booking'
import type { BookingAppointment } from '@/types/BookingAppointment'
import { computed } from 'vue'
import { Avatar, AvatarImage } from '@/components/ui/avatar'
import { getBookingStatusLabelByString, getBookingStatusBadgeClass, getBookingStatusIconByString } from '@/enums/booking/status.enum'
import { getAddressTypeLabelByString, getAddressTypeBadgeClass } from '@/enums/addresses/type.enum'
import { router } from '@inertiajs/vue3'

function cancelarCita() {
  router.post(
    route('bookings.appointments.cancel', {
      booking: props.booking.id,        // {booking}
      appointment: props.appointment.id // {appointment}
    }),
    {},
    { preserveScroll: true, preserveState: true }
  )
}


/* ==== Emits ==== */
const emit = defineEmits<{
  (e: 'openEditModal', section: 'dates' | 'children' | 'address', appointment: BookingAppointment): void
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

console.log('appointment in card', props.appointment, 'can change nanny', props.canChangeNanny, 'appointment status', props.appointment.status)


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
      <!-- CARD LEFT -->
      <div class="rounded-3xl border border-white/25 bg-white/30 dark:border-white/10 dark:bg-white/5 backdrop-blur-2xl shadow-[0_8px_30px_rgba(0,0,0,0.06)] p-4 sm:p-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between mb-5">
          <div class="leading-tight flex-1">
            <h3 class="text-xl font-semibold tracking-tight">
              {{ safeFmtReadable(props.appointment.start_date) }}
            </h3>
            <div class="mt-1 flex items-center gap-2">
              <Badge
                :class="getBookingStatusBadgeClass(props.appointment.status)"
                :icon="getBookingStatusIconByString(props.appointment.status)"
                class="px-2.5 py-0.5 text-[11px] rounded-full"
              >
                {{ getBookingStatusLabelByString(props.appointment.status) }}
              </Badge>
            </div>
          </div>

          <!-- Action buttons -->
          <div
            class="flex flex-row gap-2 items-stretch w-full md:w-auto"
          >
            <template v-if="props.appointment.status !== 'cancelled' && props.appointment.status !== 'completed'">
              <Button
                v-if="props.canEditAppointment"
                variant="outline"
                size="sm"
                class="h-8 rounded-xl hover:bg-white/50 dark:hover:bg-white/10 flex-1 md:flex-none md:w-auto"
                @click="emit('openEditModal','dates', props.appointment)"
                :title="safeGetDisabledReason(props.appointment) || 'Reprogramar fecha'"
              >
                <Icon icon="lucide:calendar-clock" class="h-4 w-4 mr-2 opacity-80" />
                <span class="opacity-90">Reprogramar</span>
              </Button>

              <Button
                variant="destructive"
                size="sm"
                class="h-8 rounded-xl flex-1 md:flex-none md:w-auto"
                @click="cancelarCita()"
              >
                <Icon icon="lucide:x-circle" class="h-4 w-4 mr-2" />
                Cancelar
              </Button>
            </template>

            <div
              v-else-if="safeGetDisabledReason(props.appointment)"
              class="text-xs text-muted-foreground"
            >
              {{ safeGetDisabledReason(props.appointment) }}
            </div>
          </div>

        </div>
        <!-- Times -->
        <div class="grid grid-cols-2 gap-4 mb-5">
          <div>
            <p class="text-[11px] text-muted-foreground mb-1 uppercase tracking-wide">Hora inicio</p>
            <p class="font-medium flex items-center gap-1.5">
              <Icon icon="lucide:clock" class="h-4 w-4 opacity-70" />
              {{ safeFmtTime(props.appointment.start_date) }}
            </p>
          </div>
          <div>
            <p class="text-[11px] text-muted-foreground mb-1 uppercase tracking-wide">Hora fin</p>
            <p class="font-medium flex items-center gap-1.5">
              <Icon icon="lucide:clock" class="h-4 w-4 opacity-70" />
              {{ safeFmtTime(props.appointment.end_date) }}
            </p>
          </div>
        </div>

        <!-- Niñera -->
        <div class="pt-4 border-t border-white/20">
          <p class="text-[11px] text-muted-foreground mb-2 uppercase tracking-wide">Niñera asignada</p>

          <!-- Nanny assigned -->
          <div v-if="props.appointment.nanny" class="space-y-3">
            <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/60 dark:bg-white/10 border border-white/30">
              <Avatar class="h-14 w-14 ring-1 ring-border">
                <AvatarImage :src="props.appointment.nanny.user.avatar_url || undefined" />
                                <p class="text-sm font-medium truncate">
                  {{ props.appointment.nanny.user.name }} {{ props.appointment.nanny.user.surnames }}
                </p>
              </Avatar>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate">
                  {{ props.appointment.nanny.user.name }} {{ props.appointment.nanny.user.surnames }}
                </p>
                <p class="text-[12px] text-muted-foreground truncate">{{ props.appointment.nanny.user.email }}</p>
              </div>
            </div>

            <!-- Change Nanny button -->
            <div v-if="props.canChangeNanny" class="text-center">
              <Button
                variant="outline"
                size="sm"
                class="w-full sm:w-auto rounded-xl"
                @click="emit('routerGet', route('bookings.appointments.nannies.choose', { booking: props.booking.id, appointment: props.appointment.id }))"
              >
                <Icon icon="lucide:repeat" class="mr-2 h-4 w-4" />
                Cambiar niñera
              </Button>
            </div>
          </div>

          <!-- No nanny assigned -->
          <div v-else class="text-center py-4 rounded-2xl border border-dashed border-white/30 bg-white/30 dark:bg-white/5">
            <div class="flex items-center justify-center gap-2 text-muted-foreground mb-3">
              <Icon icon="lucide:user-plus" class="h-4 w-4" />
              <span class="text-sm">No hay niñera asignada</span>
            </div>
            <div v-if="props.canChooseNanny">
              <Button
                variant="default"
                class="w-full sm:w-auto rounded-xl"
                @click="emit('routerGet', route('bookings.appointments.nannies.choose', { booking: props.booking.id, appointment: props.appointment.id }))"
              >
                Elegir niñera
              </Button>
            </div>
          </div>
        </div>

        <!-- Niños -->
        <div class="mt-5 pt-4 border-t border-white/20">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold flex items-center gap-2">
              <Icon icon="lucide:baby" class="h-4 w-4" /> Niños ({{ appointmentChildren.length }})
            </h3>
            <Button
              v-if="props.canEditAppointment"
              variant="ghost"
              size="sm"
              class="h-7 text-xs hover:bg-white/50 dark:hover:bg-white/10"

              @click="emit('openEditModal','children', props.appointment)"
            >
              <Icon icon="lucide:edit-3" class="h-3.5 w-3.5 mr-1" />
              Editar
            </Button>
          </div>

          <div v-if="appointmentChildren.length === 0" class="text-[13px] text-muted-foreground flex items-center gap-2 bg-white/30 dark:bg-white/5 border border-dashed border-white/30 rounded-2xl px-3 py-3">
            <Icon icon="lucide:info" class="h-4 w-4" />
            No hay niños asignados
          </div>

          <div v-else class="flex flex-col gap-2">
            <div
              v-for="child in appointmentChildren"
              :key="child.id"
              class="flex items-center gap-3 rounded-2xl border border-white/25 bg-white/50 dark:bg-white/10 backdrop-blur-md px-3 py-2.5"
            >
              <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 ring-1 ring-primary/10 shrink-0">
                <Icon icon="lucide:user-round" class="h-4 w-4 text-primary" />
              </span>
              <div class="leading-tight min-w-0">
                <p class="text-sm font-medium truncate">
                  {{ child.name }}
                  <Badge v-if="child.deleted_at" variant="outline" class="ml-1 px-1.5 py-0.5 text-[10px] rounded-full">Eliminado</Badge>
                </p>
                <p class="text-[12px] text-muted-foreground">
                  {{ child.birthdate ? new Date(child.birthdate).toLocaleDateString('es-MX') : '—' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Status messages for cancelled/completed -->
        <div v-if="props.appointment.status === 'cancelled'" class="mt-5 pt-4 border-t border-white/20">
          <div class="flex items-center justify-center gap-2 text-sm text-rose-500/90 bg-rose-50/60 dark:bg-rose-900/10 border border-rose-200/60 dark:border-rose-900/30 rounded-2xl py-2">
            <Icon icon="lucide:ban" class="h-4 w-4" />
            Cita cancelada
          </div>
        </div>

        <div v-else-if="props.appointment.status === 'completed'" class="mt-5 pt-4 border-t border-white/20">
          <div class="flex items-center justify-center gap-2 text-sm text-emerald-600/90 bg-emerald-50/70 dark:bg-emerald-900/10 border border-emerald-200/70 dark:border-emerald-800/30 rounded-2xl py-2">
            <Icon icon="lucide:check-circle2" class="h-4 w-4" />
            Cita completada
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT: Dirección + Mapa -->
    <aside class="md:col-span-4 space-y-4">
      <!-- CARD RIGHT -->
      <div class="rounded-3xl border border-white/25 bg-white/30 dark:border-white/10 dark:bg-white/5 backdrop-blur-2xl shadow-[0_8px_30px_rgba(0,0,0,0.06)] p-4 sm:p-6">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-sm font-semibold flex items-center gap-2">
            <Icon icon="lucide:map-pin" class="h-4 w-4" /> Dirección
          </h3>
          <Button
            v-if="props.canEditAppointment"
            variant="ghost"
            size="sm"
            class="h-7 text-xs hover:bg-white/50 dark:hover:bg-white/10"
            @click="emit('openEditModal','address', props.appointment)"
          >
            <Icon icon="lucide:edit-3" class="h-3.5 w-3.5 mr-1" />
            Editar
          </Button>
        </div>

        <div v-if="!appointmentAddress" class="text-[13px] text-muted-foreground flex items-center gap-2 bg-white/30 dark:bg-white/5 border border-dashed border-white/30 rounded-2xl px-3 py-3">
          <Icon icon="lucide:info" class="h-4 w-4" />
          No especificada
        </div>

        <div v-else class="space-y-3 text-[13px]">
          <div class="space-y-1.5">
            <p class="font-medium">
              {{ appointmentAddress.name }}
              <span v-if="appointmentAddress.internal_number">, Int. {{ appointmentAddress.internal_number }}</span>
            </p>
            <p class="text-muted-foreground">
              {{ appointmentAddress.street }} {{ appointmentAddress.external_number }}, {{ appointmentAddress.neighborhood }}
            </p>
            <p class="text-muted-foreground">CP: {{ appointmentAddress.postal_code }}</p>
            <Badge
              v-if="appointmentAddress.type"
              :class="getAddressTypeBadgeClass(appointmentAddress.type)"
              class="mt-1 px-2.5 py-0.5 text-[11px] rounded-full"
            >
              {{ getAddressTypeLabelByString(appointmentAddress.type) }}
            </Badge>
          </div>

          <div class="overflow-hidden rounded-2xl ring-1 ring-white/40 dark:ring-white/10">
            <GoogleMap
              :latitude="+(appointmentAddress?.latitude ?? 19.704)"
              :longitude="+(appointmentAddress?.longitude ?? -103.344)"
              :zoom="16"
              height="300px"
              :showMarker="true"
            />
          </div>
        </div>
      </div>
    </aside>
  </div>
</template>

