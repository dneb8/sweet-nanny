<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { router } from '@inertiajs/vue3'
import type { Booking } from '@/types/Booking'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { useBookingView } from '@/services/BookingService'
import { useBookingAppointmentPolicy } from '@/policies/bookingAppointmentPolicy'
import GoogleMap from '@/components/GoogleMap.vue'
import { computed } from 'vue'

const props = defineProps<{ booking: Booking }>()

const v = useBookingView(props.booking)
const policy = useBookingAppointmentPolicy()

const base =
  'group inline-flex items-center h-9 hover:gap-5 rounded-xl px-2 overflow-hidden w-9 ' +
  'transition-all duration-900 backdrop-blur-sm ' +
  'justify-center group-hover:justify-start'

const label =
  'ml-[-7px] text-sm whitespace-nowrap ' +
  'opacity-0 -translate-x-1 max-w-0 ' +
  'group-hover:opacity-100 group-hover:translate-x-0 group-hover:max-w-[6rem] ' +
  'transition-all duration-900'

const hasAnyRequirements = computed(() => {
  const qs = v.qualities?.() ?? []
  const cs = v.careers?.() ?? []
  const ks = v.courses?.() ?? []
  return qs.length || cs.length || ks.length
})


</script>

<template>
  <!-- BG gradient pastel -->
  <div class="min-h-[calc(100vh-80px)] ">
    <div class=" px-3 sm:px-6 py-6 sm:py-8">

      <!-- HEADER glass -->
      <header
        class="rounded-3xl p-4 sm:p-6 flex gap-3 flex-row items-center justify-between
              backdrop-blur-xl border border-white/30 dark:border-white/10 shadow-lg
              bg-rose-50/30 dark:bg-rose-800/5">
        <h1 class="text-xl sm:text-2xl font-semibold tracking-tight">Servicio #{{ props.booking.id }}</h1>
        <div class="flex gap-1.5">
          <!-- Editar -->
          <Button
            size="sm" variant="ghost"
            :class="`${base} hover:w-28 border border-sky-200/80 dark:border-sky-500/10 bg-sky-100/30 hover:bg-sky-100/50 dark:bg-sky-800/10 dark:hover:bg-sky-900/20 text-sky-700 dark:text-sky-200`"
            @click="v.goEdit"
            title="Editar"
          >
            <Icon icon="lucide:edit-3" class="h-4 w-4 shrink-0" />
            <span :class="label">Editar</span>
          </Button>

          <!-- Eliminar (sutil) -->
          <Button
            size="sm" variant="secondary"
            :class="`${base} hover:w-32 border border-rose-300/60 dark:border-rose-800/40 bg-rose-50/40 hover:bg-rose-200/60 dark:bg-rose-900/20 dark:hover:bg-rose-900/30 text-rose-700 dark:text-rose-200`"
            @click="v.askDelete"
            title="Eliminar"
          >
            <Icon icon="lucide:trash-2" class="h-4 w-4 shrink-0" />
            <span :class="label">Eliminar</span>
          </Button>
          
          <!-- Volver -->
          <Button
            size="sm" variant="outline"
            :class="`${base} hover:w-28`"
            @click="$inertia.get(route('bookings.index'))"
            title="Volver"
          >
            <Icon icon="lucide:arrow-left" class="h-4 w-4 shrink-0" />
            <span :class="label">Volver</span>
          </Button>
        </div>
      </header>

      <!-- CONTENT -->
      <section class="mt-4 grid gap-4 md:grid-cols-12">
        <!-- PANEL PRINCIPAL (agenda + requisitos) -->
        <div class="md:col-span-8 space-y-4">
          <!-- Resumen compacto -->
          <div class="rounded-3xl border border-white/30 bg-white/20 dark:border-white/10 dark:bg-white/5 backdrop-blur-xl shadow-lg p-4 sm:p-5">
            <h2 class="text-sm font-semibold flex items-center gap-2 mb-3">
              <Icon icon="lucide:clipboard-check" class="h-4 w-4" /> Detalles del servicio
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
              <div class="space-y-1">
                <p class="text-[11px] text-muted-foreground">Estado</p>
                <Badge :class="v.statusBadge(props.booking.status ?? 'pending')" class="px-2 py-0.5 text-[11px]">
                  {{ props.booking.status ?? 'Pendiente' }}
                </Badge>
              </div>
              <div class="space-y-1">
                <p class="text-[11px] text-muted-foreground">Creado</p>
                <p class="text-sm font-medium">{{ v.fmtDate(props.booking.created_at) }}</p>
              </div>
              <div class="space-y-1">
                <p class="text-[11px] text-muted-foreground">Recurrente</p>
                <Badge :variant="props.booking.recurrent ? 'default' : 'outline'" class="px-2 py-0.5 text-[11px]">
                  <Icon :icon="props.booking.recurrent ? 'lucide:repeat' : 'lucide:calendar'" class="mr-1 h-3 w-3" />
                  {{ props.booking.recurrent ? 'Sí' : 'No' }}
                </Badge>
              </div>
              <div class="space-y-1">
                <p class="text-[11px] text-muted-foreground">No. de Citas</p>
                <p class="text-sm font-medium flex items-center gap-1">
                  <Icon icon="lucide:calendar-clock" class="h-4 w-4 opacity-70" />
                  {{ v.appointments().length }}
                </p>
              </div>
            </div>

            <div v-if="props.booking.description" class="mt-4 rounded-2xl border border-white/20 bg-white/30 dark:bg-white/10 p-3">
              <p class="text-[11px] text-muted-foreground mb-1">Descripción</p>
              <p class="text-[13px] leading-snug">{{ props.booking.description }}</p>
            </div>
          </div>

          <!-- Citas estilo cards flotantes -->
          <div class="rounded-3xl border border-white/30 bg-white/20 dark:border-white/10 dark:bg-white/5 backdrop-blur-xl shadow-lg p-4 sm:p-5">
            <div class="flex items-center justify-between mb-3">
              <h2 class="text-sm font-semibold flex items-center gap-2">
                <Icon icon="lucide:calendar-days" class="h-4 w-4" /> Citas programadas
              </h2>
              <Badge variant="secondary" class="px-2 py-0.5 text-[11px]">{{ v.appointments().length }}</Badge>
            </div>

            <div v-if="v.appointments().length === 0" class="rounded-xl border border-dashed p-6 text-center text-sm text-muted-foreground">
              <Icon icon="lucide:calendar-x" class="mx-auto mb-2 h-7 w-7 opacity-50" />
              No hay citas programadas
            </div>

            <div v-else class="grid gap-3 sm:grid-cols-2">
              <div
                v-for="(a,i) in v.appointments()"
                :key="a.id ?? i"
                class="rounded-2xl border border-white/20 bg-white/40 dark:bg-white/10 backdrop-blur-md p-4 space-y-3 shadow"
              >
                <!-- Fecha legible -->
                <div class="space-y-1">
                  <p class="text-[11px] text-muted-foreground">Fecha de la cita</p>
                  <p class="font-semibold text-sm flex items-center gap-1.5">
                    <Icon icon="lucide:calendar-days" class="h-4 w-4 opacity-70" />
                    {{ v.fmtReadableDate(a.start_date ?? a.start_at) }}
                  </p>
                </div>

                <!-- Horarios -->
                <div class="grid grid-cols-2 gap-3 text-[13px]">
                  <div>
                    <p class="text-[11px] text-muted-foreground mb-1">Hora inicio</p>
                    <p class="font-medium flex items-center gap-1">
                      <Icon icon="lucide:clock" class="h-3.5 w-3.5 opacity-70" />
                      {{ new Date(a.start_date ?? a.start_at).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' }) }}
                    </p>
                  </div>
                  <div>
                    <p class="text-[11px] text-muted-foreground mb-1">Hora fin</p>
                    <p class="font-medium flex items-center gap-1">
                      <Icon icon="lucide:clock" class="h-3.5 w-3.5 opacity-70" />
                      {{ new Date(a.end_date ?? a.end_at).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' }) }}
                    </p>
                  </div>
                </div>

                <!-- Status y duración -->
                <div class="flex items-center justify-between">
                  <Badge :class="v.statusBadge(a.status)" class="px-2 py-0.5 text-[11px]">{{ a.status ?? 'Pendiente' }}</Badge>
                  <span v-if="a.duration" class="text-[11px] text-muted-foreground">{{ a.duration }} min</span>
                </div>

                <div v-if="a.nanny" class="text-[12px] text-muted-foreground">
                  Niñera: <span class="text-foreground font-medium">{{ a.nanny.user.name + ' ' + a.nanny.user.surnames }}</span>
                </div>

                <!-- usa policy.canChooseNanny -->
                <div v-else-if="policy.canChooseNanny(a, props.booking)" class="pt-2">
                  <Button
                    size="sm"
                    variant="default"
                    class="w-full text-[11px] h-7"
                    @click="router.get(route('bookings.appointments.nannies.choose', { booking: props.booking.id, appointment: a.id }))"
                  >
                    <Icon icon="lucide:user-plus" class="mr-1.5 h-3.5 w-3.5" />
                    Elegir niñera
                  </Button>
                </div>

                <div v-if="a.notes" class="text-[12px] leading-snug pt-2 border-t border-white/20">
                  {{ a.notes }}
                </div>

                <!-- Cancel appointment button -->
                <div v-if="a.status !== 'cancelled'" class="pt-2 border-t border-white/20">
                  <Button
                    size="sm"
                    variant="destructive"
                    class="w-full text-[11px] h-7"
                    @click="v.cancelAppointment(a.id)"
                  >
                    <Icon icon="lucide:x-circle" class="mr-1.5 h-3.5 w-3.5" />
                    Cancelar cita
                  </Button>
                </div>
                <div v-else class="pt-2 border-t border-white/20">
                  <div class="flex items-center justify-center gap-2 text-[11px] text-muted-foreground py-1">
                    <Icon icon="lucide:ban" class="h-3.5 w-3.5" />
                    <span>Cita cancelada</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Requisitos  -->
          <div class="rounded-3xl border border-white/30 bg-white/20 dark:border-white/10 dark:bg-white/5 backdrop-blur-xl shadow-lg p-4 sm:p-5">
            <h2 class="mb-4 sm:mb-5 flex items-center gap-2 text-sm font-semibold">
              <Icon icon="lucide:clipboard-check" class="h-4 w-4 shrink-0" /> Requisitos de la niñera
            </h2>

            <!-- Empty state -->
            <div v-if="!hasAnyRequirements" class="flex items-center gap-3 rounded-2xl border border-dashed p-4 text-sm text-muted-foreground">
              <Icon icon="lucide:info" class="h-4 w-4" />
              No hay requisitos establecidos para esta solicitud.
            </div>

            <!-- Grid de secciones -->
            <div v-else class="grid grid-cols-1 gap-4 sm:gap-5">
              <!-- Qualities -->
              <section v-if="v.qualities() !== null">
                <p class="mb-2 text-[11px] uppercase tracking-wide text-muted-foreground">Cualidades</p>
                <div v-if="v.qualities().length===0" class="text-[13px] text-muted-foreground">—</div>
                <div v-else class="flex flex-wrap gap-1.5 sm:gap-2 max-sm:overflow-x-auto max-sm:[&>*]:shrink-0 max-sm:pb-1">
                  <Badge v-for="q in v.qualities()" :key="q" :class="[v.qualityBadge(),'px-2 py-0.5 text-[11px] sm:text-[12px] leading-5']">
                    {{ v.enumLabel(q,'quality') }}
                  </Badge>
                </div>
              </section>

              <!-- Careers -->
              <section v-if="v.careers() !== null">
                <p class="mb-2 text-[11px] uppercase tracking-wide text-muted-foreground">Carreras</p>
                <div v-if="v.careers().length===0" class="text-[13px] text-muted-foreground">—</div>
                <div v-else class="flex flex-wrap gap-1.5 sm:gap-2 max-sm:overflow-x-auto max-sm:[&>*]:shrink-0 max-sm:pb-1">
                  <Badge v-for="c in v.careers()" :key="c" :class="[v.careerBadge(),'px-2 py-0.5 text-[11px] sm:text-[12px] leading-5 inline-flex items-center']">
                    <Icon icon="lucide:graduation-cap" class="mr-1 h-3 w-3" />
                    <span class="line-clamp-1">{{ v.enumLabel(c,'career') }}</span>
                  </Badge>
                </div>
              </section>

              <!-- Courses -->
              <section v-if="v.courses() !== null">
                <p class="mb-2 text-[11px] uppercase tracking-wide text-muted-foreground">Cursos</p>
                <div v-if="v.courses().length===0" class="text-[13px] text-muted-foreground">—</div>
                <div v-else class="flex flex-wrap gap-1.5 sm:gap-2 max-sm:overflow-x-auto max-sm:[&>*]:shrink-0 max-sm:pb-1">
                  <Badge v-for="c in v.courses()" :key="c" :class="[v.courseBadge(),'px-2 py-0.5 text-[11px] sm:text-[12px] leading-5 inline-flex items-center']">
                    <Icon icon="lucide:book-open" class="mr-1 h-3 w-3" />
                    <span class="line-clamp-1">{{ v.enumLabel(c,'course') }}</span>
                  </Badge>
                </div>
              </section>
            </div>
          </div>
        </div>

        <!-- PANEL LATERAL (perfil & dirección) -->
        <aside class="md:col-span-4 space-y-4">
          <!-- Tutor -->
          <div class="rounded-3xl border border-white/30 bg-white/25 dark:border-white/10 dark:bg-white/5 backdrop-blur-xl shadow-lg p-4 sm:p-5">
            <div class="flex items-center gap-3 mb-2">
              <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                <Icon icon="lucide:user" class="h-5 w-5 text-primary" />
              </div>
              <div>
                <p class="text-sm font-medium leading-tight">{{ props.booking.tutor?.user.name ?? '—' }}</p>
                <p class="text-[12px] text-muted-foreground">{{ props.booking.tutor?.user.email ?? '' }}</p>
              </div>
            </div>
            <div class="flex flex-wrap gap-1 text-[11px] text-muted-foreground">
              <Badge v-if="props.booking.recurrent" class="px-2 py-0.5 bg-white/50 dark:bg-white/10">Recurrente</Badge>
            </div>
          </div>

          <!-- Dirección -->
          <div class="rounded-3xl border border-white/30 bg-white/25 dark:border-white/10 dark:bg-white/5 backdrop-blur-xl shadow-lg p-4 sm:p-5">
            <h3 class="text-sm font-semibold mb-2 flex items-center gap-2">
              <Icon icon="lucide:map-pin" class="h-4 w-4" /> Dirección
            </h3>
            <div v-if="!v.address()" class="text-[13px] text-muted-foreground">No especificada</div>
            <div v-else class="space-y-3 text-[13px]">
              <div class="space-y-1.5">
                <p class="font-medium">
                  {{ v.address().street }}
                  <span v-if="v.address().internal_number">, Int. {{ v.address().internal_number }}</span>
                </p>
                <p class="text-muted-foreground">{{ v.address().neighborhood }}</p>
                <p class="text-muted-foreground">{{ v.address().postal_code }}</p>
                <Badge v-if="v.address().type" variant="secondary" class="mt-1 px-2 py-0.5 text-[11px]">
                  {{ v.address().type }}
                </Badge>
              </div>
              
              <!-- Map -->
               <GoogleMap
                :latitude="+(v.address()?.latitude ?? 19.704)"
                :longitude="+(v.address()?.longitude ?? -103.344)"
                :zoom="16"
                height="320px"
                :showMarker="true"
              />

            </div>
          </div>

          <!-- Niños -->
          <div class="rounded-3xl border border-white/30 bg-white/25 dark:border-white/10 dark:bg-white/5 backdrop-blur-xl shadow-lg p-4 sm:p-5">
            <h3 class="text-sm font-semibold mb-2 flex items-center gap-2">
              <Icon icon="lucide:baby" class="h-4 w-4" /> Niños ({{ v.children().length }})
            </h3>

            <div v-if="v.children().length === 0"
                class="text-[13px] text-muted-foreground">
              No hay niños asignados
            </div>

            <div v-else class="flex flex-col gap-2">
              <div
                v-for="c in v.children()"
                :key="c.id"
                class="flex items-center justify-between rounded-xl border border-white/20 bg-white/40 dark:bg-white/10 backdrop-blur-md px-3 py-2"
              >
                <div class="flex items-center gap-2">
                  <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                    <Icon icon="lucide:user-round" class="h-4 w-4 text-primary" />
                  </span>
                  <div class="leading-tight">
                    <p class="text-sm font-medium">
                      {{ c.name }}
                      <Badge v-if="c.deleted_at" variant="outline" class="ml-2 px-2 py-0.5 text-[10px]">Eliminado</Badge>
                    </p>
                    <p class="text-[12px] text-muted-foreground">
                      {{ c.birthdate ? new Date(c.birthdate).toLocaleDateString('es-MX') : '—' }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </aside>
      </section>
    </div>
  </div>
</template>
