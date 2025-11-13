import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import type { PageProps } from '@/types/UsePage'
import type { Booking } from '@/types/Booking'
import type { BookingAppointment } from '@/types/BookingAppointment'
import { useBookingAppointmentPolicy } from '@/policies/bookingAppointmentPolicy'
import { getQualityLabelByString as qLabel } from '@/enums/quality.enum'
import { getCareerNameLabelByString as careerLabel } from '@/enums/careers/career-name.enum'
import { getCourseNameLabelByString as courseLabel } from '@/enums/courses/course-name.enum'

const MX_TZ = 'America/Mexico_City'

/** Add hours to a date without mutating */
function addHours(date: Date, hours: number): Date {
  const d = new Date(date.getTime())
  d.setHours(d.getHours() + hours)
  return d
}

/* Formateadores */
const fmtDateTZ = (iso: string) => {
  const date = new Date(iso)
  const shifted = addHours(date, 6)
  return new Intl.DateTimeFormat('es-MX', { dateStyle: 'medium', timeZone: MX_TZ }).format(shifted)
}

const fmtTimeTZ = (iso: string) => {
  const date = new Date(iso)
  const shifted = addHours(date, 6)
  return new Intl.DateTimeFormat('es-MX', { hour: 'numeric', minute: '2-digit', hour12: true, timeZone: MX_TZ }).format(shifted)
}

const fmtReadableDateTime = (iso: string) => `${fmtDateTZ(iso)} · ${fmtTimeTZ(iso)}`
const fmtDateTime = (value?: string | Date | null) => {
  if (!value) return '—'
  const d = typeof value === 'string' ? new Date(value) : value
  return isNaN(+d)
    ? String(value)
    : new Intl.DateTimeFormat('es-MX', { dateStyle: 'medium', timeStyle: 'short' }).format(d)
}
const fmtDate = (value?: string | Date | null) => {
  if (!value) return '—'
  const d = typeof value === 'string' ? new Date(value) : value
  return isNaN(+d)
    ? String(value)
    : new Intl.DateTimeFormat('es-MX', { dateStyle: 'medium' }).format(d)
}
const fmtReadableDate = (value?: string | Date | null) => {
  if (!value) return '—'
  const d = typeof value === 'string' ? new Date(value) : value
  return isNaN(+d)
    ? String(value)
    : new Intl.DateTimeFormat('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }).format(d)
}

/* Badges */
const statusBadge = (status?: string) => {
  switch ((status ?? '').toLowerCase()) {
    case 'pending': return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-400/20 dark:text-yellow-200 dark:border-yellow-200'
    case 'confirmed': return 'bg-sky-100 text-sky-700 dark:bg-sky-400/20 dark:text-sky-200 dark:border-sky-200'
    case 'completed': return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/20 dark:text-emerald-200 dark:border-emerald-200'
    case 'cancelled': return 'bg-red-100 text-red-700 dark:bg-red-400/20 dark:text-red-200 dark:border-red-200'
    default: return 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200 dark:border-slate-200'
  }
}
const qualityBadge = () => 'bg-purple-200 text-purple-900 dark:text-purple-200 dark:bg-purple-900/60 dark:border-purple-200'
const careerBadge = () => 'bg-indigo-200 text-indigo-900 dark:text-indigo-100 dark:bg-indigo-500/40 dark:border-indigo-200'
const courseBadge = () => 'bg-emerald-200 text-emerald-900 dark:text-emerald-100 dark:bg-emerald-900/60 dark:border-emerald-200'

/* Labels */
const enumLabel = (val: string, type: 'quality' | 'career' | 'course') =>
  type === 'quality' ? qLabel(val) : type === 'career' ? careerLabel(val) : courseLabel(val)

export function useBookingService(booking: Booking) {
  const page = usePage<PageProps>()
  const user = computed(() => (page.props.auth?.user ?? {}) as any)

  // Roles
  const roleNames = computed(() =>
    (user.value?.roles ?? [])
      .map((r: any) => (typeof r === 'string' ? r : r?.name))
      .filter(Boolean)
      .map((r: string) => r.toLowerCase())
  )
  const isAdmin = computed(() => roleNames.value.includes('admin'))
  const isTutor = computed(() => roleNames.value.includes('tutor'))
  const isNanny = computed(() => roleNames.value.includes('nanny'))

  /* Booking actions */
  const showDeleteModal = ref(false)
  const goShow = () => router.get(route('bookings.show', booking.id))
  const goEdit = () => router.get(route('bookings.edit', booking.id))
  const askDelete = () => (showDeleteModal.value = true)
  const confirmDelete = () => router.delete(route('bookings.destroy', booking.id))

  /* Data accessors */
  const appointments = () => booking.booking_appointments ?? []
  const children = () => appointments()[0]?.children ?? []
  const careers = () => booking.careers ?? []
  const qualities = () => booking.qualities ?? []
  const courses = () => booking.courses ?? []
  const address = () => appointments()[0]?.addresses?.[0] ?? null

  /* Selected appointment */
  const firstId = appointments()[0]?.id ?? null
  const selectedAppointmentId = ref<number | null>(firstId)
  const selectedAppointment = computed<BookingAppointment | null>(() => {
  const id = selectedAppointmentId.value
    return appointments().find(a => a.id === id) ?? null
  })

  // Si cambia la lista de citas, valida el id seleccionado
  watch(
    () => appointments().map(a => a.id).join(','),
    async () => {
      await nextTick()
      const ids = appointments().map(a => a.id)
      if (!ids.includes(selectedAppointmentId.value as number)) {
        selectedAppointmentId.value = ids[0] ?? null
      }
    },
    { immediate: false }
  )

  // Modelo de tabs (string) sincronizado con selectedAppointmentId
  const tabsModel = computed<string>({
    get() {
      const id = selectedAppointmentId.value ?? appointments()[0]?.id
      return id != null ? String(id) : ''
    },
    set(val: string) {
      const n = Number(val)
      selectedAppointmentId.value = Number.isFinite(n) ? n : null
    },
  })

  /* Policies */
  const policy = useBookingAppointmentPolicy()
  const canChooseNanny = (appt: BookingAppointment) => policy.canChooseNanny(appt, booking)
  const canChangeNanny = (appt: BookingAppointment) => policy.canChangeNanny(appt, booking)
  const canEditAppointment = () => {
    const ap = selectedAppointment.value
    return ap ? policy.canEdit(ap, booking) : false
  }

  /* Modals */
  const showDatesModal = ref(false)
  const showAddressModal = ref(false)
  const showChildrenModal = ref(false)
  const showConfirmModal = ref(false)
  const showConfirmChangeNannyModal = ref(false)
  const pendingModalAction = ref<'dates' | 'address' | 'children' | null>(null)
  const pendingChangeNannyAppointment = ref<BookingAppointment | null>(null)

  function needsConfirmation(type: 'dates' | 'address' | 'children'): boolean {
    const ap = selectedAppointment.value
    if (!ap) return false
    const status = String(ap.status ?? '').toLowerCase()
    if (status !== 'pending') return false
    if (!ap.nanny_id) return false
    return type === 'dates' || type === 'address'
  }

  function openEditModal(type: 'dates' | 'address' | 'children') {
    if (!canEditAppointment()) return
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
  function closeConfirmChangeNannyModal() { showConfirmChangeNannyModal.value = false; pendingChangeNannyAppointment.value = null }

  function handleChangeNanny(appointment: BookingAppointment) {
    pendingChangeNannyAppointment.value = appointment
    showConfirmChangeNannyModal.value = true
  }
  function confirmChangeNanny() {
    if (pendingChangeNannyAppointment.value) {
      router.get(route('bookings.appointments.nannies.choose', { booking: booking.id, appointment: pendingChangeNannyAppointment.value.id }))
    }
    closeConfirmChangeNannyModal()
  }

  /* Al guardar cambios, recarga */
  function handleModalSaved() {
    showDatesModal.value = false
    showAddressModal.value = false
    showChildrenModal.value = false
    showConfirmModal.value = false
    pendingModalAction.value = null

    router.visit(route('bookings.show', { booking: booking.id }), {
      replace: true,
      preserveScroll: true,
      preserveState: false,
      only: ['booking'],
      onSuccess: () => {
        const first = appointments()[0]?.id ?? null
        selectedAppointmentId.value = selectedAppointmentId.value ?? first
      },
    })
  }

  /* Helpers */
  const hasAnyAppointmentWithNanny = computed(() => appointments().some(a => a.nanny_id !== null))
  const hasPendingAppointments = computed(() => appointments().some(a => String(a.status).toLowerCase() === 'pending'))
  const deleteMessage = computed(() => {
    if (hasPendingAppointments.value) {
      return 'Este servicio tiene citas pendientes con niñeras asignadas. Al eliminarlo, se cancelarán todas las citas. ¿Deseas continuar?'
    }
    return '¿Estás seguro de que deseas eliminar este servicio? Esta acción no se puede deshacer.'
  })

  function getEditDisabledReason(appointment: BookingAppointment): string {
    const status = String(appointment.status).toLowerCase()
    if (status === 'confirmed') return 'Cita confirmada - no editable'
    if (status === 'in_progress') return 'Cita en progreso - no editable'
    if (status === 'completed') return 'Cita completada - no editable'
    if (status === 'cancelled') return 'Cita cancelada - no editable'
    return ''
  }

  // Scroll auto (opcional)
  const scrollContainer = ref<HTMLElement | null>(null)
  let scrollInterval: number | null = null
  let dir = 1
  onMounted(() => {
    const el = scrollContainer.value?.querySelector('[data-scroll-content]') as HTMLElement
    if (!el) return
    scrollInterval = window.setInterval(() => {
      if (el.scrollLeft + el.clientWidth >= el.scrollWidth) dir = -1
      else if (el.scrollLeft <= 0) dir = 1
      el.scrollLeft += dir * 0.5
    }, 16)
  })
  onUnmounted(() => { if (scrollInterval) clearInterval(scrollInterval) })

  // Cancelar cita
  const cancelAppointment = (appointmentId: string) => {
    router.post(route('bookings.appointments.cancel', { booking: booking.id, appointment: appointmentId }))
  }

  return {
    // user/roles
    user, roleNames, isAdmin, isTutor, isNanny,

    // actions booking
    showDeleteModal, goShow, goEdit, askDelete, confirmDelete,

    // data accessors
    appointments, children, careers, qualities, courses, address,

    // selection/tabs
    selectedAppointmentId, selectedAppointment, tabsModel,

    // policy-derived
    canChooseNanny, canChangeNanny, canEditAppointment,

    // modals + flows
    showDatesModal, showAddressModal, showChildrenModal, showConfirmModal,
    showConfirmChangeNannyModal, openEditModal, confirmUnassign,
    closeDatesModal, closeAddressModal, closeChildrenModal, closeConfirmModal,
    closeConfirmChangeNannyModal, handleChangeNanny, confirmChangeNanny,
    handleModalSaved,

    // helpers UI
    hasAnyAppointmentWithNanny, hasPendingAppointments, deleteMessage,
    getEditDisabledReason,

    // formatters
    fmtDateTime, fmtDate, fmtReadableDate, fmtDateTZ, fmtTimeTZ, fmtReadableDateTime,
    enumLabel, statusBadge, qualityBadge, careerBadge, courseBadge,

    // misc
    scrollContainer, cancelAppointment,
  }
}
