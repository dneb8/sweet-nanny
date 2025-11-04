<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { router } from '@inertiajs/vue3'
import type { Booking } from '@/types/Booking'
import type { BookingAppointment } from '@/types/BookingAppointment'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { useBookingView } from '@/services/BookingService'
import { useBookingAppointmentPolicy } from '@/policies/bookingAppointmentPolicy'
import GoogleMap from '@/components/GoogleMap.vue'
import { computed, ref } from 'vue'
import EditAppointmentDatesModal from './components/modals/EditAppointmentDatesModal.vue'
import EditAppointmentAddressModal from './components/modals/EditAppointmentAddressModal.vue'
import EditAppointmentChildrenModal from './components/modals/EditAppointmentChildrenModal.vue'
import ConfirmUnassignModal from './components/modals/ConfirmUnassignModal.vue'

const props = defineProps<{ 
  booking: Booking
  kinkships: string[]
}>()

const v = useBookingView(props.booking)
const policy = useBookingAppointmentPolicy()

const selectedAppointmentId = ref<number | null>(v.appointments()[0]?.id ?? null)
const selectedAppointment = computed(() => 
  v.appointments().find(a => a.id === selectedAppointmentId.value) ?? null
)

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

// Get children for selected appointment
const appointmentChildren = computed(() => selectedAppointment.value?.children ?? [])
const appointmentAddress = computed(() => selectedAppointment.value?.addresses?.[0] ?? null)

// Modal states
const showDatesModal = ref(false)
const showAddressModal = ref(false)
const showChildrenModal = ref(false)
const showConfirmModal = ref(false)
const pendingModalAction = ref<'dates' | 'address' | null>(null)

// Check if appointment can be edited
const canEditAppointment = computed(() => {
  if (!selectedAppointment.value) return false
  return policy.canEdit(selectedAppointment.value, props.booking)
})

// Check if editing needs confirmation (pending status with dates/address)
function needsConfirmation(type: 'dates' | 'address' | 'children'): boolean {
  if (!selectedAppointment.value) return false
  if (selectedAppointment.value.status !== 'pending') return false
  if (!selectedAppointment.value.nanny_id) return false
  return type === 'dates' || type === 'address'
}

// Open edit modal with confirmation if needed
function openEditModal(type: 'dates' | 'address' | 'children') {
  if (!canEditAppointment.value) return
  
  if (needsConfirmation(type)) {
    pendingModalAction.value = type
    showConfirmModal.value = true
  } else {
    // Open modal directly
    if (type === 'dates') showDatesModal.value = true
    else if (type === 'address') showAddressModal.value = true
    else if (type === 'children') showChildrenModal.value = true
  }
}

// Confirm unassignment and open modal
function confirmUnassign() {
  showConfirmModal.value = false
  if (pendingModalAction.value === 'dates') showDatesModal.value = true
  else if (pendingModalAction.value === 'address') showAddressModal.value = true
  pendingModalAction.value = null
}

// Close modals
function closeDatesModal() { showDatesModal.value = false }
function closeAddressModal() { showAddressModal.value = false }
function closeChildrenModal() { showChildrenModal.value = false }
function closeConfirmModal() { 
  showConfirmModal.value = false 
  pendingModalAction.value = null
}

// Handle modal save (reload page)
function handleModalSaved() {
  router.reload({ only: ['booking'] })
}

// Get reason why appointment can't be edited
function getEditDisabledReason(appointment: BookingAppointment): string {
  if (appointment.status === 'confirmed') return 'Cita confirmada - no editable'
  if (appointment.status === 'in_progress') return 'Cita en progreso - no editable'
  if (appointment.status === 'completed') return 'Cita completada - no editable'
  if (appointment.status === 'cancelled') return 'Cita cancelada - no editable'
  return ''
}

</script>

<template>
  <div class="min-h-[calc(100vh-80px)]">
    <div class="px-3 sm:px-6 py-6 sm:py-8">

      <!-- BOOKING HEADER -->
      <header
        class="rounded-3xl p-4 sm:p-6 backdrop-blur-xl border border-white/30 dark:border-white/10 shadow-lg
              bg-rose-50/30 dark:bg-rose-800/5 space-y-4">
        
        <!-- Title and actions -->
        <div class="flex gap-3 flex-row items-center justify-between">
          <div>
            <h1 class="text-xl sm:text-2xl font-semibold tracking-tight">Servicio #{{ props.booking.id }}</h1>
            <p v-if="props.booking.description" class="text-sm text-muted-foreground mt-1">
              {{ props.booking.description }}
            </p>
          </div>
          <div class="flex gap-1.5">
            <Button
              size="sm" variant="ghost"
              :class="`${base} hover:w-28 border border-sky-200/80 dark:border-sky-500/10 bg-sky-100/30 hover:bg-sky-100/50 dark:bg-sky-800/10 dark:hover:bg-sky-900/20 text-sky-700 dark:text-sky-200`"
              @click="v.goEdit"
              title="Editar"
            >
              <Icon icon="lucide:edit-3" class="h-4 w-4 shrink-0" />
              <span :class="label">Editar</span>
            </Button>

            <Button
              size="sm" variant="secondary"
              :class="`${base} hover:w-32 border border-rose-300/60 dark:border-rose-800/40 bg-rose-50/40 hover:bg-rose-200/60 dark:bg-rose-900/20 dark:hover:bg-rose-900/30 text-rose-700 dark:text-rose-200`"
              @click="v.askDelete"
              title="Eliminar"
            >
              <Icon icon="lucide:trash-2" class="h-4 w-4 shrink-0" />
              <span :class="label">Eliminar</span>
            </Button>
          
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
        </div>

        <!-- Booking metadata -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div class="space-y-1">
            <p class="text-[11px] text-muted-foreground">Tutor</p>
            <p class="text-sm font-medium">{{ props.booking.tutor?.user.name ?? '—' }}</p>
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

        <!-- Requirements -->
        <div v-if="hasAnyRequirements" class="rounded-2xl border border-white/20 bg-white/30 dark:bg-white/10 p-4">
          <h3 class="text-sm font-semibold mb-3 flex items-center gap-2">
            <Icon icon="lucide:clipboard-check" class="h-4 w-4" /> Requisitos de la niñera
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Qualities -->
            <div v-if="v.qualities()?.length">
              <p class="mb-2 text-[11px] uppercase tracking-wide text-muted-foreground">Cualidades</p>
              <div class="flex flex-wrap gap-1.5">
                <Badge v-for="q in v.qualities()" :key="q" :class="[v.qualityBadge(),'px-2 py-0.5 text-[11px]']">
                  {{ v.enumLabel(q,'quality') }}
                </Badge>
              </div>
            </div>

            <!-- Careers -->
            <div v-if="v.careers()?.length">
              <p class="mb-2 text-[11px] uppercase tracking-wide text-muted-foreground">Carreras</p>
              <div class="flex flex-wrap gap-1.5">
                <Badge v-for="c in v.careers()" :key="c" :class="[v.careerBadge(),'px-2 py-0.5 text-[11px] inline-flex items-center']">
                  <Icon icon="lucide:graduation-cap" class="mr-1 h-3 w-3" />
                  {{ v.enumLabel(c,'career') }}
                </Badge>
              </div>
            </div>

            <!-- Courses -->
            <div v-if="v.courses()?.length">
              <p class="mb-2 text-[11px] uppercase tracking-wide text-muted-foreground">Cursos</p>
              <div class="flex flex-wrap gap-1.5">
                <Badge v-for="c in v.courses()" :key="c" :class="[v.courseBadge(),'px-2 py-0.5 text-[11px] inline-flex items-center']">
                  <Icon icon="lucide:book-open" class="mr-1 h-3 w-3" />
                  {{ v.enumLabel(c,'course') }}
                </Badge>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- APPOINTMENTS TABS -->
      <section class="mt-4">
        <div v-if="v.appointments().length === 0" class="rounded-3xl border border-white/30 bg-white/20 dark:border-white/10 dark:bg-white/5 backdrop-blur-xl shadow-lg p-8 text-center">
          <Icon icon="lucide:calendar-x" class="mx-auto mb-3 h-12 w-12 opacity-50 text-muted-foreground" />
          <p class="text-sm text-muted-foreground">No hay citas programadas para este servicio</p>
        </div>

        <Tabs v-else :default-value="String(v.appointments()[0]?.id)" class="w-full" @update:model-value="(val) => selectedAppointmentId = Number(val)">
          <TabsList class="w-full grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 h-auto p-2 bg-white/5 backdrop-blur-xl border border-white/30 dark:border-white/10">
            <TabsTrigger
              v-for="(appointment, idx) in v.appointments().slice(0, 10)"
              :key="appointment.id"
              :value="String(appointment.id)"
              class="data-[state=active]:bg-white dark:data-[state=active]:bg-white/10 data-[state=active]:shadow-sm flex flex-col items-start p-3 h-auto text-left"
            >
              <span class="text-[10px] text-muted-foreground mb-1">Cita {{ idx + 1 }}</span>
              <span class="text-xs font-medium line-clamp-2">{{ v.fmtReadableDate(appointment.start_date) }}</span>
            </TabsTrigger>
          </TabsList>

          <TabsContent
            v-for="appointment in v.appointments().slice(0, 10)"
            :key="appointment.id"
            :value="String(appointment.id)"
            class="mt-4"
          >
            <div class="grid gap-4 md:grid-cols-12">
              <!-- Appointment details - left column -->
              <div class="md:col-span-8 space-y-4">
                <!-- Appointment time and status -->
                <div class="rounded-3xl border border-white/30 bg-white/20 dark:border-white/10 dark:bg-white/5 backdrop-blur-xl shadow-lg p-4 sm:p-5">
                  <div class="flex items-start justify-between mb-4">
                    <div>
                      <h3 class="text-lg font-semibold mb-1">{{ v.fmtReadableDate(appointment.start_date) }}</h3>
                      <div class="flex items-center gap-2">
                        <Badge :class="v.statusBadge(appointment.status)" class="px-2 py-0.5 text-[11px]">
                          {{ appointment.status ?? 'Pendiente' }}
                        </Badge>
                        <Badge v-if="appointment.payment_status" variant="outline" class="px-2 py-0.5 text-[11px]">
                          {{ appointment.payment_status }}
                        </Badge>
                      </div>
                    </div>
                    <div v-if="canEditAppointment">
                      <Button
                        variant="ghost"
                        size="sm"
                        @click="openEditModal('dates')"
                        :title="getEditDisabledReason(appointment) || 'Editar fechas'"
                      >
                        <Icon icon="lucide:calendar-clock" class="h-4 w-4 mr-2" />
                        Editar fechas
                      </Button>
                    </div>
                    <div v-else-if="getEditDisabledReason(appointment)" class="text-xs text-muted-foreground">
                      {{ getEditDisabledReason(appointment) }}
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                      <p class="text-[11px] text-muted-foreground mb-1">Hora inicio</p>
                      <p class="font-medium flex items-center gap-1">
                        <Icon icon="lucide:clock" class="h-4 w-4 opacity-70" />
                        {{ new Date(appointment.start_date).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' }) }}
                      </p>
                    </div>
                    <div>
                      <p class="text-[11px] text-muted-foreground mb-1">Hora fin</p>
                      <p class="font-medium flex items-center gap-1">
                        <Icon icon="lucide:clock" class="h-4 w-4 opacity-70" />
                        {{ new Date(appointment.end_date).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' }) }}
                      </p>
                    </div>
                  </div>

                  <div v-if="appointment.total_cost" class="mb-4">
                    <p class="text-[11px] text-muted-foreground mb-1">Costo total</p>
                    <p class="text-xl font-semibold">${{ appointment.total_cost }}</p>
                  </div>

                  <!-- Nanny section -->
                  <div class="pt-4 border-t border-white/20">
                    <p class="text-[11px] text-muted-foreground mb-2">Niñera asignada</p>
                    <div v-if="appointment.nanny" class="flex items-center gap-3 p-3 rounded-xl bg-white/40 dark:bg-white/10">
                      <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                        <Icon icon="lucide:user" class="h-5 w-5 text-primary" />
                      </div>
                      <div>
                        <p class="text-sm font-medium">{{ appointment.nanny.user.name }} {{ appointment.nanny.user.surnames }}</p>
                        <p class="text-[12px] text-muted-foreground">{{ appointment.nanny.user.email }}</p>
                      </div>
                    </div>
                    <div v-else-if="policy.canChooseNanny(appointment, props.booking)" class="text-center py-4">
                      <p class="text-sm text-muted-foreground mb-3">No hay niñera asignada</p>
                      <Button
                        variant="default"
                        class="w-full sm:w-auto"
                        @click="router.get(route('bookings.appointments.nannies.choose', { booking: props.booking.id, appointment: appointment.id }))"
                      >
                        <Icon icon="lucide:user-plus" class="mr-2 h-4 w-4" />
                        Elegir niñera
                      </Button>
                    </div>
                    <div v-else class="text-center py-4">
                      <p class="text-sm text-muted-foreground">No hay niñera asignada</p>
                    </div>
                  </div>

                  <!-- Action buttons -->
                  <div class="flex gap-2 mt-4 pt-4 border-t border-white/20">
                    <Button
                      v-if="appointment.status !== 'cancelled'"
                      variant="destructive"
                      size="sm"
                      class="flex-1"
                      @click="v.cancelAppointment(appointment.id)"
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

              <!-- Address and Children - right column -->
              <aside class="md:col-span-4 space-y-4">
                <!-- Address -->
                <div class="rounded-3xl border border-white/30 bg-white/25 dark:border-white/10 dark:bg-white/5 backdrop-blur-xl shadow-lg p-4 sm:p-5">
                  <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold flex items-center gap-2">
                      <Icon icon="lucide:map-pin" class="h-4 w-4" /> Dirección
                    </h3>
                    <Button
                      v-if="canEditAppointment"
                      variant="ghost"
                      size="sm"
                      @click="openEditModal('address')"
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
                        {{ appointmentAddress.street }}
                        <span v-if="appointmentAddress.internal_number">, Int. {{ appointmentAddress.internal_number }}</span>
                      </p>
                      <p class="text-muted-foreground">{{ appointmentAddress.neighborhood }}</p>
                      <p class="text-muted-foreground">{{ appointmentAddress.postal_code }}</p>
                      <Badge v-if="appointmentAddress.type" variant="secondary" class="mt-1 px-2 py-0.5 text-[11px]">
                        {{ appointmentAddress.type }}
                      </Badge>
                    </div>
                    
                    <GoogleMap
                      :latitude="+(appointmentAddress?.latitude ?? 19.704)"
                      :longitude="+(appointmentAddress?.longitude ?? -103.344)"
                      :zoom="16"
                      class="min-h-[280px] sm:min-h-[320px]"
                      height="320px"
                      :showMarker="true"
                    />
                  </div>
                </div>

                <!-- Children -->
                <div class="rounded-3xl border border-white/30 bg-white/25 dark:border-white/10 dark:bg-white/5 backdrop-blur-xl shadow-lg p-4 sm:p-5">
                  <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold flex items-center gap-2">
                      <Icon icon="lucide:baby" class="h-4 w-4" /> Niños ({{ appointmentChildren.length }})
                    </h3>
                    <Button
                      v-if="canEditAppointment"
                      variant="ghost"
                      size="sm"
                      @click="openEditModal('children')"
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
              </aside>
            </div>
          </TabsContent>
        </Tabs>
      </section>
    </div>

    <!-- Edit Modals -->
    <Dialog :open="showDatesModal" @update:open="(val) => !val && closeDatesModal()">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Editar fechas de la cita</DialogTitle>
        </DialogHeader>
        <EditAppointmentDatesModal
          v-if="selectedAppointment"
          :appointment="selectedAppointment"
          :booking="booking"
          @close="closeDatesModal"
          @saved="handleModalSaved"
        />
      </DialogContent>
    </Dialog>

    <Dialog :open="showAddressModal" @update:open="(val) => !val && closeAddressModal()">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Editar dirección de la cita</DialogTitle>
        </DialogHeader>
        <EditAppointmentAddressModal
          v-if="selectedAppointment"
          :appointment="selectedAppointment"
          :booking="booking"
          @close="closeAddressModal"
          @saved="handleModalSaved"
        />
      </DialogContent>
    </Dialog>

    <Dialog :open="showChildrenModal" @update:open="(val) => !val && closeChildrenModal()">
      <DialogContent class="max-w-3xl">
        <DialogHeader>
          <DialogTitle>Editar niños de la cita</DialogTitle>
        </DialogHeader>
        <EditAppointmentChildrenModal
          v-if="selectedAppointment"
          :appointment="selectedAppointment"
          :booking="booking"
          :kinkships="kinkships"
          @close="closeChildrenModal"
          @saved="handleModalSaved"
        />
      </DialogContent>
    </Dialog>

    <ConfirmUnassignModal
      :show="showConfirmModal"
      @close="closeConfirmModal"
      @confirm="confirmUnassign"
    />
  </div>
</template>
