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
import { computed, ref, watch, nextTick } from 'vue'
import EditAppointmentDatesModal from './components/modals/EditAppointmentDatesModal.vue'
import EditAppointmentAddressModal from './components/modals/EditAppointmentAddressModal.vue'
import EditAppointmentChildrenModal from './components/modals/EditAppointmentChildrenModal.vue'
import ConfirmUnassignModal from './components/modals/ConfirmUnassignModal.vue'
import ConfirmChangeNannyModal from './components/modals/ConfirmChangeNannyModal.vue'
import DeleteModal from '@/components/common/DeleteModal.vue'
import SingleAppointmentCard from './components/SingleAppointmentCard.vue'

const props = defineProps<{ 
  booking: Booking
  kinkships: string[]
  can?: {
    update?: boolean
    delete?: boolean
  }
}>()

const v = useBookingView(props.booking)
const policy = useBookingAppointmentPolicy()

// ----------------- selección de cita activa -----------------
const firstId = v.appointments()[0]?.id ?? null
const selectedAppointmentId = ref<number | null>(firstId)

const selectedAppointment = computed<BookingAppointment | null>(() => {
  const id = selectedAppointmentId.value
  return v.appointments().find(a => a.id === id) ?? null
})

// Si las citas cambian (por reload), asegura que haya una seleccionada válida
watch(
  () => v.appointments().map(a => a.id).join(','), // firma mínima de cambios
  async () => {
    await nextTick()
    const ids = v.appointments().map(a => a.id)
    if (!ids.includes(selectedAppointmentId.value as number)) {
      selectedAppointmentId.value = ids[0] ?? null
    }
  },
  { immediate: false }
)

const tabsModel = computed<string>({
  get() {
    const id = selectedAppointmentId.value ?? v.appointments()[0]?.id
    return id != null ? String(id) : ''
  },
  set(val: string) {
    const n = Number(val)
    selectedAppointmentId.value = Number.isFinite(n) ? n : null
  },
})

// ----------------- estilos botones header -----------------
const base =
  'group inline-flex items-center h-9 hover:gap-5 rounded-xl px-2 overflow-hidden w-9 ' +
  'transition-all duration-900 backdrop-blur-sm ' +
  'justify-center group-hover:justify-start'

const label =
  'ml-[-7px] text-sm whitespace-nowrap ' +
  'opacity-0 -translate-x-1 max-w-0 ' +
  'group-hover:opacity-100 group-hover:translate-x-0 group-hover:max-w-[6rem] ' +
  'transition-all duration-900'

// ----------------- datos derivados -----------------
const hasAnyRequirements = computed(() => {
  const qs = v.qualities?.() ?? []
  const cs = v.careers?.() ?? []
  const ks = v.courses?.() ?? []
  return qs.length || cs.length || ks.length
})

const hasAnyAppointmentWithNanny = computed(() => {
  return v.appointments().some(a => a.nanny_id !== null)
})

const hasPendingAppointments = computed(() => {
  return v.appointments().some(a => a.status === 'pending')
})

const deleteMessage = computed(() => {
  if (hasPendingAppointments.value) {
    return 'Este servicio tiene citas pendientes con niñeras asignadas. Al eliminarlo, se cancelarán todas las citas. ¿Deseas continuar?'
  }
  return '¿Estás seguro de que deseas eliminar este servicio? Esta acción no se puede deshacer.'
})

// ----------------- permisos helpers -----------------
function canChooseNanny(appointment: BookingAppointment): boolean {
  return policy.canChooseNanny(appointment, props.booking)
}

function canChangeNanny(appointment: BookingAppointment): boolean {
  // Debe existir una niñera asignada para "cambiar"
  if (!appointment?.nanny_id) return false

  const roles = (window as any)?.$page?.props?.auth?.roles || []
  const isAdmin = roles.includes('admin')
  const isTutor = roles.includes('tutor')
  const status = appointment?.status

  // Admin: permitido salvo estados terminales
  if (isAdmin) {
    return status !== 'cancelled' && status !== 'completed'
  }

  // Tutor: estrictamente cuando está pendiente
  if (isTutor) {
    return status === 'pending'
  }

  // Otros roles: no
  return false
}


// ----------------- fechas helpers -----------------
const MX_TZ = 'America/Mexico_City'
function fmtDateTZ(iso: string) {
  return new Intl.DateTimeFormat('es-MX', { dateStyle: 'medium', timeZone: MX_TZ }).format(new Date(iso))
}
function fmtTimeTZ(iso: string) {
  return new Intl.DateTimeFormat('es-MX', { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: MX_TZ }).format(new Date(iso))
}
function fmtReadableDateTime(iso: string) {
  const d = fmtDateTZ(iso)
  const t = fmtTimeTZ(iso)
  return `${d} · ${t} h`
}

// ----------------- modals -----------------
const showDatesModal = ref(false)
const showAddressModal = ref(false)
const showChildrenModal = ref(false)
const showConfirmModal = ref(false)
const showConfirmChangeNannyModal = ref(false)
const pendingModalAction = ref<'dates' | 'address' | 'children' | null>(null)
const pendingChangeNannyAppointment = ref<BookingAppointment | null>(null)

const canEditAppointment = computed(() => {
  if (!selectedAppointment.value) return false
  return policy.canEdit(selectedAppointment.value, props.booking)
})

function needsConfirmation(type: 'dates' | 'address' | 'children'): boolean {
  if (!selectedAppointment.value) return false
  if (selectedAppointment.value.status !== 'pending') return false
  if (!selectedAppointment.value.nanny_id) return false
  return type === 'dates' || type === 'address'
}

function openEditModal(type: 'dates' | 'address' | 'children') {
  if (!canEditAppointment.value) return
  if (needsConfirmation(type)) {
    pendingModalAction.value = type
    showConfirmModal.value = true
  } else {
    if (type === 'dates') showDatesModal.value = true
    else if (type === 'address') showAddressModal.value = true
    else if (type === 'children') showChildrenModal.value = true
  }
}

function confirmUnassign() {
  showConfirmModal.value = false
  if (pendingModalAction.value === 'dates') showDatesModal.value = true
  else if (pendingModalAction.value === 'address') showAddressModal.value = true
  pendingModalAction.value = null
}

function closeDatesModal() { showDatesModal.value = false }
function closeAddressModal() { showAddressModal.value = false }
function closeChildrenModal() { showChildrenModal.value = false }
function closeConfirmModal() { showConfirmModal.value = false; pendingModalAction.value = null }
function closeConfirmChangeNannyModal() { 
  showConfirmChangeNannyModal.value = false
  pendingChangeNannyAppointment.value = null
}

// Handle change nanny button click - show confirmation modal first
function handleChangeNanny(appointment: BookingAppointment) {
  pendingChangeNannyAppointment.value = appointment
  showConfirmChangeNannyModal.value = true
}

// After confirmation, proceed to nanny selection
function confirmChangeNanny() {
  if (pendingChangeNannyAppointment.value) {
    router.get(route('bookings.appointments.nannies.choose', { 
      booking: props.booking.id, 
      appointment: pendingChangeNannyAppointment.value.id 
    }))
  }
  closeConfirmChangeNannyModal()
}

// ----------------- RELOAD FORZADO tras guardar -----------------
// Llama este método desde los modales al terminar la acción en servidor
function handleModalSaved() {
  // Cierra modales abiertos primero
  showDatesModal.value = false
  showAddressModal.value = false
  showChildrenModal.value = false
  showConfirmModal.value = false
  pendingModalAction.value = null

  // Visita explícitamente el show para evitar props “stale”
  router.visit(route('bookings.show', { booking: props.booking.id }), {
    replace: true,           // no contamina el historial
    preserveScroll: true,    // evita salto de scroll
    preserveState: false,    // NO conservar estado cacheado
    only: ['booking'],       // pide solo el prop necesario
    onSuccess: () => {
      // Opcional: asegura que la pestaña activa exista tras el reload
      const first = v.appointments()[0]?.id ?? null
      selectedAppointmentId.value = selectedAppointmentId.value ?? first
    },
  })
}

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
            <h1 class="text-xl sm:text-2xl font-semibold tracking-tight">
              {{ props.booking.recurrent ? 'Servicio recurrente' : 'Servicio fijo' }} #{{ props.booking.id }}
            </h1>
            <p v-if="props.booking.description" class="text-sm text-muted-foreground mt-1">
              {{ props.booking.description }}
            </p>
          </div>
          <div class="flex gap-1.5">
            <Button
              v-if="props.can?.update"
              size="sm" variant="ghost"
              :class="`${base} hover:w-28 border border-sky-200/80 dark:border-sky-500/10 bg-sky-100/30 hover:bg-sky-100/50 dark:bg-sky-800/10 dark:hover:bg-sky-900/20 text-sky-700 dark:text-sky-200`"
              @click="v.goEdit"
              title="Editar servicio"
            >
              <Icon icon="lucide:edit-3" class="h-4 w-4 shrink-0" />
              <span :class="label">Editar</span>
            </Button>
            <div 
              v-else-if="hasAnyAppointmentWithNanny"
              class="text-xs text-muted-foreground px-3 py-2 rounded-lg bg-amber-50/50 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-800/40"
              title="No se puede editar porque al menos un appointment tiene niñera asignada"
            >
              <Icon icon="lucide:info" class="h-3 w-3 inline-block mr-1" />
              <span class="hidden sm:inline">No editable - tiene niñera asignada</span>
            </div>

            <Button
              v-if="props.can?.delete"
              size="sm" variant="secondary"
              :class="`${base} hover:w-32 border border-rose-300/60 dark:border-rose-800/40 bg-rose-50/40 hover:bg-rose-200/60 dark:bg-rose-900/20 dark:hover:bg-rose-900/30 text-rose-700 dark:text-rose-200`"
              @click="v.askDelete"
              title="Eliminar servicio"
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
            <p class="text-sm font-medium">{{ fmtDateTZ(props.booking.created_at) }}</p>
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
            <div v-if="v.qualities()?.length">
              <p class="mb-2 text-[11px] uppercase tracking-wide text-muted-foreground">Cualidades</p>
              <div class="flex flex-wrap gap-1.5">
                <Badge v-for="q in v.qualities()" :key="q" :class="[v.qualityBadge(),'px-2 py-0.5 text-[11px]']">
                  {{ v.enumLabel(q,'quality') }}
                </Badge>
              </div>
            </div>

            <div v-if="v.careers()?.length">
              <p class="mb-2 text-[11px] uppercase tracking-wide text-muted-foreground">Carreras</p>
              <div class="flex flex-wrap gap-1.5">
                <Badge v-for="c in v.careers()" :key="c" :class="[v.careerBadge(),'px-2 py-0.5 text-[11px] inline-flex items-center']">
                  <Icon icon="lucide:graduation-cap" class="mr-1 h-3 w-3" />
                  {{ v.enumLabel(c,'career') }}
                </Badge>
              </div>
            </div>

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

      <!-- APPOINTMENTS -->
      <section class="mt-4">
        <div v-if="v.appointments().length === 0" class="rounded-3xl border border-white/30 bg-white/20 dark:border-white/10 dark:bg-white/5 backdrop-blur-xl shadow-lg p-8 text-center">
          <Icon icon="lucide:calendar-x" class="mx-auto mb-3 h-12 w-12 opacity-50 text-muted-foreground" />
          <p class="text-sm text-muted-foreground">No hay citas programadas para este servicio</p>
        </div>

        <!-- Recurrente: Tabs -->
        <template v-if="props.booking.recurrent && v.appointments().length > 0">
          <Tabs v-model="tabsModel" class="w-full">
            <TabsList class="w-full grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 h-auto p-2 bg-white/5 backdrop-blur-xl border border-white/30 dark:border-white/10">
              <TabsTrigger
                v-for="(appointment, idx) in v.appointments().slice(0, 10)"
                :key="appointment.id"
                :value="String(appointment.id)"
                class="data-[state=active]:bg-white dark:data-[state=active]:bg-white/10 data-[state=active]:shadow-sm flex flex-col items-start p-3 h-auto text-left"
              >
                <span class="text-[10px] text-muted-foreground mb-1">Cita {{ idx + 1 }}</span>
                <span class="text-xs font-medium line-clamp-2">{{ fmtReadableDateTime(appointment.start_date) }}</span>
              </TabsTrigger>
            </TabsList>

            <TabsContent
              v-for="appointment in v.appointments().slice(0, 10)"
              :key="appointment.id"
              :value="String(appointment.id)"
              class="mt-4"
            >
              <SingleAppointmentCard
                :appointment="appointment"
                :booking="props.booking"
                :can-edit-appointment="policy.canEdit(appointment, props.booking)"
                :can-choose-nanny="canChooseNanny(appointment)"
                :can-change-nanny="canChangeNanny(appointment)"
                :get-edit-disabled-reason="getEditDisabledReason"
                :fmt-readable-date-time="fmtReadableDateTime"
                :fmt-time-tz="fmtTimeTZ"
                @openEditModal="openEditModal"
                @handleModalSaved="handleModalSaved"
                @changeNanny="handleChangeNanny(appointment)"
                @routerGet="(params: any) => router.get(params)" />
            </TabsContent>
          </Tabs>
        </template>

        <!-- Fijo: una sola card -->
        <template v-else-if="!props.booking.recurrent && v.appointments().length > 0">
          <div class="mt-2">
            <SingleAppointmentCard
              :appointment="v.appointments()[0]"
              :booking="props.booking"
              :can-edit-appointment="policy.canEdit(v.appointments()[0], props.booking)"
              :can-choose-nanny="canChooseNanny(v.appointments()[0])"
              :can-change-nanny="canChangeNanny(v.appointments()[0])"
              :get-edit-disabled-reason="getEditDisabledReason"
              :fmt-readable-date-time="fmtReadableDateTime"
              :fmt-time-tz="fmtTimeTZ"
              @openEditModal="openEditModal"
              @handleModalSaved="handleModalSaved"
              @changeNanny="handleChangeNanny(v.appointments()[0])"
              @routerGet="(params: any) => router.get(params)" />
          </div>
        </template>
      </section>
    </div>

    <!-- Edit Modals -->
    <Dialog :open="showDatesModal" @update:open="(val: boolean) => !val && closeDatesModal()">
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

    <Dialog :open="showAddressModal" @update:open="(val: boolean) => !val && closeAddressModal()">
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

    <Dialog :open="showChildrenModal" @update:open="(val: boolean) => !val && closeChildrenModal()">
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

    <!-- Confirm Change Nanny Modal -->
    <ConfirmChangeNannyModal
      :show="showConfirmChangeNannyModal"
      @close="closeConfirmChangeNannyModal"
      @confirm="confirmChangeNanny"
    />

    <!-- Delete Modal -->
    <DeleteModal
      :show="v.showDeleteModal.value"
      :message="deleteMessage"
      :onConfirm="v.confirmDelete"
      :onCancel="() => v.showDeleteModal.value = false"
      @update:show="(val) => v.showDeleteModal.value = val"
    />
  </div>
</template>
