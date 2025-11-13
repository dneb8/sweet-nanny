// resources/js/policies/bookingAppointmentPolicy.ts
import { usePage } from '@inertiajs/vue3'
import type { PageProps } from '@/types/UsePage'
import type { Booking } from '@/types/Booking'
import type { BookingAppointment } from '@/types/BookingAppointment'
import { hasRole, canOrRole } from '@/helpers/permissionHelper'

function normId(id: unknown): string {
  if (id == null) return ''
  return String(id).trim()
}

function isTerminal(status?: string) {
  const s = String(status ?? '').toLowerCase()
  return s === 'cancelled' || s === 'completed'
}

export function useBookingAppointmentPolicy() {
  const page = usePage<PageProps>()
  const user = page.props.auth?.user
  const userId = normId(user?.ulid ?? user?.id)

  function belongsToTutor(booking?: Booking): boolean {
    if (!booking) return false
    const bookingTutorId = normId(
      (booking as any)?.tutor?.user_id ?? (booking as any)?.tutor?.user?.ulid ?? (booking as any)?.tutor?.user?.id
    )
    return bookingTutorId !== '' && bookingTutorId === userId
  }

  /** Elegir niñera: sin niñera, en 'draft'; admin o tutor dueño con permiso */
  function canChooseNanny(appt: BookingAppointment, booking?: Booking): boolean {
    const status = String(appt?.status ?? '').toLowerCase()
    if ((appt as any)?.nanny_id) return false
    if (status !== 'draft') return false

    if (hasRole('admin')) return true

    const isOwnerTutor = hasRole('tutor') && belongsToTutor(booking)
    return canOrRole('booking_appointment.choose_nanny', isOwnerTutor)
  }

  /** Editar: solo draft/pending; admin o tutor dueño */
  function canEdit(appt: BookingAppointment, booking?: Booking): boolean {
    if (!['draft', 'pending'].includes(String(appt.status).toLowerCase())) return false
    if (hasRole('admin')) return true
    if (hasRole('tutor') && booking) {
      return belongsToTutor(booking)
    }
    return false
  }

  /** Cambiar niñera: requiere niñera asignada.
   *  Admin: permitido salvo estados terminales.
   *  Tutor: solo si es dueño y status 'pending'.
   */
  function canChangeNanny(appt: BookingAppointment, booking?: Booking): boolean {
    const status = String(appt?.status ?? '').toLowerCase()
    const hasNanny = !!(appt as any)?.nanny_id
    if (!hasNanny) return false

    if (hasRole('admin')) return !isTerminal(status)

    if (hasRole('tutor') && belongsToTutor(booking)) {
      return status === 'pending'
    }
    return false
  }

  return { canChooseNanny, canEdit, canChangeNanny }
}
