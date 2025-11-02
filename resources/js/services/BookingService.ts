import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import type { Booking } from '@/types/Booking'

// enums → labels
import { getQualityLabelByString as qLabel } from '@/enums/quality.enum'
import { getCareerNameLabelByString as careerLabel } from '@/enums/careers/career-name.enum'
import { getCourseNameLabelByString as courseLabel } from '@/enums/courses/course-name.enum'

export function useBookingView(booking: Booking) {
  // navegación / acciones
  const showDeleteModal = ref(false)
  const goShow = () => router.get(route('bookings.show', booking.id))
  const goEdit = () => router.get(route('bookings.edit', booking.id))
  const askDelete = () => (showDeleteModal.value = true)
  const confirmDelete = () => router.delete(route('bookings.destroy', booking.id))

  // formateadores
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
  
  // Formato completo de fecha legible (ej: "miércoles, 5 de febrero de 2025")
  const fmtReadableDate = (value?: string | Date | null) => {
    if (!value) return '—'
    const d = typeof value === 'string' ? new Date(value) : value
    return isNaN(+d)
      ? String(value)
      : new Intl.DateTimeFormat('es-MX', { 
          weekday: 'long', 
          year: 'numeric', 
          month: 'long', 
          day: 'numeric' 
        }).format(d)
  }

  // labels genéricos para arrays de enums
  const enumLabel = (val: string, type: 'quality' | 'career' | 'course') =>
    type === 'quality' ? qLabel(val) : type === 'career' ? careerLabel(val) : courseLabel(val)

  // badges por estado (opcional, si usas status/payment_status)
  const statusBadge = (status?: string) => {
    switch (status) {
      case 'pending': return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-400/20 dark:text-yellow-200'
      case 'confirmed': return 'bg-sky-100 text-sky-700 dark:bg-sky-400/20 dark:text-sky-200'
      case 'completed': return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/20 dark:text-emerald-200'
      case 'cancelled': return 'bg-red-100 text-red-700 dark:bg-red-400/20 dark:text-red-200'
      default: return 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200'
    }
  }

  // Consistent badge colors for Qualities, Careers, and Courses
  const qualityBadge = () => 'bg-purple-200 text-purple-900 dark:text-purple-200 dark:bg-purple-900/60 dark:border-purple-200'
  const careerBadge = () => 'bg-indigo-200 text-indigo-900 dark:text-indigo-100 dark:bg-indigo-500/40 dark:border-indigo-200'
  const courseBadge = () => 'bg-emerald-200 text-emerald-900 dark:text-emerald-100 dark:bg-emerald-900/60 dark:border-emerald-200'

  // auto-scroll suave (si lo necesitas en una fila horizontal)
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

  // helpers compactos para vista
  const children = () => booking.children ?? []
  const appointments = () => booking.booking_appointments ?? []
  const careers = () => booking.careers ?? []
  const qualities = () => booking.qualities ?? []
  const courses = () => booking.courses ?? []

  // Cancel appointment
  const cancelAppointment = (appointmentId: string) => {
    router.post(route('bookings.appointments.cancel', { booking: booking.id, appointment: appointmentId }))
  }

  return {
    // actions
    showDeleteModal, goShow, goEdit, askDelete, confirmDelete, cancelAppointment,
    // format
    fmtDateTime, fmtDate, fmtReadableDate, enumLabel, statusBadge, qualityBadge, careerBadge, courseBadge,
    // data accessors
    children, appointments, careers, qualities, courses,
    // scroll
    scrollContainer,
  }
}

// Export estandarizado para consumidores: mismo API que useBookingView
export function useBookingService(booking: Booking) {
  return useBookingView(booking)
}
