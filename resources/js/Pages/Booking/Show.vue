<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { router } from '@inertiajs/vue3'
import type { Booking } from '@/types/Booking'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { computed } from 'vue'

import EditAppointmentDatesModal from './components/modals/EditAppointmentDatesModal.vue'
import EditAppointmentAddressModal from './components/modals/EditAppointmentAddressModal.vue'
import EditAppointmentChildrenModal from './components/modals/EditAppointmentChildrenModal.vue'
import CtaModal from '@/components/common/CtaModal.vue'
import DeleteModal from '@/components/common/DeleteModal.vue'
import SingleAppointmentCard from './components/SingleAppointmentCard.vue'

import { useBookingService } from '@/services/BookingService'

const props = defineProps<{ 
  booking: Booking
  kinkships: string[]
  openAppointmentId?: number | null
  can?: { update?: boolean; delete?: boolean }
}>()

const svc = useBookingService(props.booking)

// Botones del header
const base =
  'group inline-flex items-center h-9 hover:gap-5 rounded-xl px-2 overflow-hidden w-9 ' +
  'transition-all duration-900 backdrop-blur-sm justify-center group-hover:justify-start'
const label =
  'ml-[-7px] text-sm whitespace-nowrap opacity-0 -translate-x-1 max-w-0 ' +
  'group-hover:opacity-100 group-hover:translate-x-0 group-hover:max-w-[6rem] transition-all duration-900'

// Verificar si hay cualidades, carreras o cursos
const hasAnyRequirements = computed(() => {
  const qs = svc.qualities() ?? []
  const cs = svc.careers() ?? []
  const ks = svc.courses() ?? []
  return qs.length || cs.length || ks.length
})
</script>

<template>
  <div class="min-h-[calc(100vh-80px)]">
    <div class="px-3 sm:px-6 py-6 sm:py-8">
      <!-- CABECERA -->
      <header
        class="rounded-3xl p-4 sm:p-6 backdrop-blur-xl border border-white/30 dark:border-white/10 shadow-lg bg-rose-50/30 dark:bg-rose-800/5 space-y-4">

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
            <!-- Botón Editar -->
            <Button
              v-if="props.can?.update"
              size="sm" variant="ghost"
              :class="`${base} hover:w-28 border border-sky-200/80 dark:border-sky-500/10 bg-sky-100/30 hover:bg-sky-100/50 dark:bg-sky-800/10 dark:hover:bg-sky-900/20 text-sky-700 dark:text-sky-200`"
              @click="svc.goEdit"
              title="Editar servicio"
            >
              <Icon icon="lucide:edit-3" class="h-4 w-4 shrink-0" />
              <span :class="label">Editar</span>
            </Button>
            <!-- Mensaje si no se puede editar -->
            <div 
              v-else-if="svc.hasAnyAppointmentWithNanny"
              class="text-xs text-muted-foreground px-3 py-2 rounded-lg bg-amber-50/50 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-800/40"
              title="No se puede editar porque al menos un appointment tiene niñera asignada"
            >
              <Icon icon="lucide:info" class="h-3 w-3 inline-block mr-1" />
              <span class="hidden sm:inline">No editable - tiene niñera asignada</span>
            </div>

            <!-- Botón Eliminar -->
            <Button
              v-if="props.can?.delete"
              size="sm" variant="secondary"
              :class="`${base} hover:w-32 border border-rose-300/60 dark:border-rose-800/40 bg-rose-50/40 hover:bg-rose-200/60 dark:bg-rose-900/20 dark:hover:bg-rose-900/30 text-rose-700 dark:text-rose-200`"
              @click="svc.askDelete"
              title="Eliminar servicio"
            >
              <Icon icon="lucide:trash-2" class="h-4 w-4 shrink-0" />
              <span :class="label">Eliminar</span>
            </Button>
          
            <!-- Botón Volver -->
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

        <!-- Metadata -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div class="space-y-1">
            <p class="text-[11px] text-muted-foreground">Tutor</p>
            <p class="text-sm font-medium">{{ props.booking.tutor?.user?.name ?? '—' }}</p>
          </div>
          <div class="space-y-1">
            <p class="text-[11px] text-muted-foreground">Creado</p>
            <p class="text-sm font-medium">{{ svc.fmtDateTZ(props.booking.created_at) }}</p>
          </div>
          <div class="space-y-1">
            <p class="text-[11px] text-muted-foreground">No. de Citas</p>
            <p class="text-sm font-medium flex items-center gap-1">
              <Icon icon="lucide:calendar-clock" class="h-4 w-4 opacity-70" />
              {{ svc.appointments().length }}
            </p>
          </div>
        </div>

        <!-- Requisitos -->
        <div v-if="hasAnyRequirements" class="rounded-2xl border border-white/20 bg-white/30 dark:bg-white/10 p-4">
          <h3 class="text-sm font-semibold mb-3 flex items-center gap-2">
            <Icon icon="lucide:clipboard-check" class="h-4 w-4" /> Requisitos de la niñera
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Cualidades -->
            <div v-if="svc.qualities()?.length">
              <p class="mb-2 text-[11px] uppercase tracking-wide text-muted-foreground">Cualidades</p>
              <div class="flex flex-wrap gap-1.5">
                <Badge v-for="q in svc.qualities()" :key="q" :class="[svc.qualityBadge(),'px-2 py-0.5 text-[11px]']">
                  {{ svc.enumLabel(q,'quality') }}
                </Badge>
              </div>
            </div>
            <!-- Carreras -->
            <div v-if="svc.careers()?.length">
              <p class="mb-2 text-[11px] uppercase tracking-wide text-muted-foreground">Carreras</p>
              <div class="flex flex-wrap gap-1.5">
                <Badge v-for="c in svc.careers()" :key="c" :class="[svc.careerBadge(),'px-2 py-0.5 text-[11px] inline-flex items-center']">
                  <Icon icon="lucide:graduation-cap" class="mr-1 h-3 w-3" />
                  {{ svc.enumLabel(c,'career') }}
                </Badge>
              </div>
            </div>
            <!-- Cursos -->
            <div v-if="svc.courses()?.length">
              <p class="mb-2 text-[11px] uppercase tracking-wide text-muted-foreground">Cursos</p>
              <div class="flex flex-wrap gap-1.5">
                <Badge v-for="c in svc.courses()" :key="c" :class="[svc.courseBadge(),'px-2 py-0.5 text-[11px] inline-flex items-center']">
                  <Icon icon="lucide:book-open" class="mr-1 h-3 w-3" />
                  {{ svc.enumLabel(c,'course') }}
                </Badge>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- APPOINTMENTS -->
      <section class="mt-4">
        <div v-if="svc.appointments().length === 0" class="rounded-3xl border border-white/30 bg-white/20 dark:border-white/10 dark:bg-white/5 backdrop-blur-xl shadow-lg p-8 text-center">
          <Icon icon="lucide:calendar-x" class="mx-auto mb-3 h-12 w-12 opacity-50 text-muted-foreground" />
          <p class="text-sm text-muted-foreground">No hay citas programadas para este servicio</p>
        </div>

        <!-- Tabs para servicios recurrentes -->
        <template v-if="props.booking.recurrent && svc.appointments().length > 0">
        <Tabs
          :default-value="String(props.openAppointmentId || (svc.appointments()[0] && svc.appointments()[0].id) || '')"
          class="w-full"
        >
          <TabsList
            class="w-full grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 h-auto p-2 bg-white/5 backdrop-blur-xl border border-white/30 dark:border-white/10"
          >
            <TabsTrigger
              v-for="(appointment, idx) in svc.appointments().slice(0, 10)"
              :key="appointment.id"
              :value="String(appointment.id)"
              :class="['data-[state=active]:shadow-sm flex flex-col items-start p-3 h-auto text-left', svc.statusBadge(appointment.status)]"
            >
              <span class="text-[10px] opacity-70 mb-1">Cita {{ idx + 1 }}</span>
              <span class="text-xs font-medium line-clamp-2">
                {{ svc.fmtReadableDateTime(appointment.start_date) }}
              </span>
            </TabsTrigger>
          </TabsList>

          <TabsContent
            v-for="appointment in svc.appointments().slice(0, 10)"
            :key="appointment.id"
            :value="String(appointment.id)"
            class="mt-4"
          >
            <SingleAppointmentCard
              :appointment="appointment"
              :booking="props.booking"
              :can-edit-appointment="svc.canEditAppointment()"
              :can-choose-nanny="svc.canChooseNanny(appointment)"
              :can-change-nanny="svc.canChangeNanny(appointment)"
              :get-edit-disabled-reason="svc.getEditDisabledReason"
              :fmt-readable-date-time="svc.fmtReadableDateTime"
              :fmt-time-tz="svc.fmtTimeTZ"
              @openEditModal="svc.openEditModal"
              @handleModalSaved="svc.handleModalSaved"
              @routerGet="(params: any) => router.get(params)"
            />
          </TabsContent>
        </Tabs>

        </template>

        <!-- Para servicios no recurrentes: mostrar una sola tarjeta -->
        <template v-else-if="!props.booking.recurrent && svc.appointments().length > 0">
          <div class="mt-2">
            <SingleAppointmentCard
              :appointment="svc.appointments()[0]"
              :booking="props.booking"
              :can-edit-appointment="svc.canEditAppointment()"
              :can-choose-nanny="svc.canChooseNanny(svc.appointments()[0])"
              :can-change-nanny="svc.canChangeNanny(svc.appointments()[0])"
              :get-edit-disabled-reason="svc.getEditDisabledReason"
              :fmt-readable-date-time="svc.fmtReadableDateTime"
              :fmt-time-tz="svc.fmtTimeTZ"
              @openEditModal="svc.openEditModal"
              @handleModalSaved="svc.handleModalSaved"
              @routerGet="(params: any) => router.get(params)" />
          </div>
        </template>
      </section>
    </div>

    <!-- MODALES -->

    <Dialog :open="svc.showDatesModal.value" @update:open="(val: boolean) => { svc.showDatesModal.value = val; if (!val) svc.closeDatesModal() }">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Editar fechas de la cita</DialogTitle>
        </DialogHeader>
        <EditAppointmentDatesModal
          v-if="svc.selectedAppointment"
          :appointment="svc.selectedAppointment"
          :booking="booking"
          @close="svc.closeDatesModal"
          @saved="svc.handleModalSaved"
        />
      </DialogContent>
    </Dialog>

    <Dialog :open="svc.showAddressModal.value" @update:open="(val: boolean) => { svc.showAddressModal.value = val; if (!val) svc.closeAddressModal() }">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Editar dirección de la cita</DialogTitle>
        </DialogHeader>
        <EditAppointmentAddressModal
          v-if="svc.selectedAppointment"
          :appointment="svc.selectedAppointment"
          :booking="booking"
          @close="svc.closeAddressModal"
          @saved="svc.handleModalSaved"
        />
      </DialogContent>
    </Dialog>

    <Dialog :open="svc.showChildrenModal.value" @update:open="(val: boolean) => { svc.showChildrenModal.value = val; if (!val) svc.closeChildrenModal() }">
      <DialogContent class="max-w-3xl">
        <DialogHeader>
          <DialogTitle>Editar niños de la cita</DialogTitle>
        </DialogHeader>
        <EditAppointmentChildrenModal
          v-if="svc.selectedAppointment"
          :appointment="svc.selectedAppointment"
          :booking="booking"
          :kinkships="kinkships"
          @close="svc.closeChildrenModal"
          @saved="svc.handleModalSaved"
        />
      </DialogContent>
    </Dialog>

    <!-- Confirmar desasignar niñera -->
    <CtaModal
      :show="svc.showConfirmModal.value"
      type="warning"
      title="Confirmación necesaria"
      message="Al editar fechas o dirección en una cita con estado pendiente, la niñera asignada será removida y la cita volverá a estado borrador. ¿Deseas continuar con esta acción?"
      confirmText="Sí, continuar"
      :onConfirm="svc.confirmUnassign"
      :onCancel="svc.closeConfirmModal"
      @update:show="(val: boolean) => { svc.showConfirmModal.value = val; if (!val) svc.closeConfirmModal() }"
    />

    <!-- Confirmar cambio de niñera -->
    <CtaModal
      :show="svc.showConfirmChangeNannyModal.value"
      type="warning"
      title="Confirmar cambio de niñera"
      message="Estás a punto de cambiar la niñera asignada a esta cita. La niñera actual será notificada de que su asignación ha sido cancelada. ¿Deseas continuar con esta acción?"
      confirmText="Sí, cambiar niñera"
      :onConfirm="svc.confirmChangeNanny"
      :onCancel="svc.closeConfirmChangeNannyModal"
      @update:show="(val: boolean) => { svc.showConfirmChangeNannyModal.value = val; if (!val) svc.closeConfirmChangeNannyModal() }"
    />

    <!-- Modal de eliminación -->
    <DeleteModal
      :show="svc.showDeleteModal.value"
      :message="svc.deleteMessage.value"
      :onConfirm="svc.confirmDelete"
      :onCancel="() => { svc.showDeleteModal.value = false }"
      @update:show="(val: boolean) => { svc.showDeleteModal.value = val }"
    />
  </div>
</template>
