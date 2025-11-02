import { usePage } from '@inertiajs/vue3'
import type { PageProps } from '@/types/UsePage'
import type { Booking } from '@/types/Booking'
import type { BookingAppointment } from '@/types/BookingAppointment'
import { hasRole, canOrRole, getRoles } from '@/helpers/permissionHelper'

const DEV = typeof import.meta !== 'undefined' && import.meta.env?.DEV

/** Normaliza ID a string (ulid/uuid/number) y garantiza '' cuando no existe */
function normId(id: unknown): string {
  if (id == null) return ''
  return String(id).trim()
}

export function useBookingAppointmentPolicy() {
  const page = usePage<PageProps>()
  const user = page.props.auth?.user
  const userId = normId(user?.ulid ?? user?.id) // usa el que realmente sea tu “ID” canónico
  const roleNames = getRoles()

  if (DEV) {
    console.groupCollapsed('[policy] init useBookingAppointmentPolicy')
    console.table({ userId, userName: user?.name ?? '—', roles: roleNames.join(', ') || '—' })
    console.groupEnd()
  }

  function canChooseNanny(appt: BookingAppointment, booking?: Booking): boolean {
    if (DEV) {
      console.groupCollapsed('[policy] canChooseNanny')
      console.table({
        appt_id: (appt as any)?.id ?? '—',
        appt_nanny_id: (appt as any)?.nanny_id ?? null,
        booking_id: booking?.id ?? '—',
        booking_tutor_user_id: normId(booking?.tutor?.user_id ?? (booking as any)?.tutor?.user?.ulid),
        current_user_id: userId || '—',
        user_roles: roleNames.join(', ') || '—',
      })
    }

    // 1) si ya tiene niñera, no permitir elegir
    if ((appt as any)?.nanny_id) { if (DEV) console.groupEnd(); return false }

    // 2) admin siempre puede
    if (hasRole('admin')) { if (DEV) console.groupEnd(); return true }

    // 3) tutor dueño del booking (owner)
    const bookingTutorId = normId(booking?.tutor?.user_id ?? (booking as any)?.tutor?.user?.ulid)
    const isOwnerTutor = hasRole('tutor') && !!booking && bookingTutorId !== '' && bookingTutorId === userId

    // 4) permiso explícito; si no hay permisos cargados, usa fallback por rol (owner)
    const allowed = canOrRole('booking_appointment.choose_nanny', isOwnerTutor)

    if (DEV) {
      console.table({ isOwnerTutor, allowed })
      console.groupEnd()
    }
    return allowed
  }

  return { canChooseNanny }
}
