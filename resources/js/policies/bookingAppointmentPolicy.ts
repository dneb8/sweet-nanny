import { usePage } from '@inertiajs/vue3'
import type { PageProps } from '@/types/UsePage'
import type { Booking } from '@/types/Booking'
import type { BookingAppointment } from '@/types/BookingAppointment'
import { hasRole, canOrRole, getRoles } from '@/helpers/permissionHelper'

const DEV = typeof import.meta !== 'undefined' && import.meta.env?.DEV

export function useBookingAppointmentPolicy() {
  const page = usePage<PageProps>()
  const user = page.props.auth.user
  const userId = user?.id
  const roleNames = getRoles()

  if (DEV) {
    console.groupCollapsed('[policy] init useBookingAppointmentPolicy')
    console.table({ userId, userName: user?.name, roles: roleNames.join(', ') || '—' })
    console.groupEnd()
  }

  function canChooseNanny(appt: BookingAppointment, booking?: Booking): boolean {
    if (DEV) {
      console.groupCollapsed('[policy] canChooseNanny call')
      console.table({
        appt_id: (appt as any)?.id ?? '—',
        appt_nanny_id: (appt as any)?.nanny_id ?? null,
        booking_id: booking?.id ?? '—',
        booking_tutor_user_id: booking?.tutor?.user_id ?? '—',
        current_user_id: userId ?? '—',
        user_roles: roleNames.join(', ') || '—',
      })
    }

    // 1) si ya tiene niñera, no mostrar
    if ((appt as any)?.nanny_id) { if (DEV) console.groupEnd(); return false }

    // 2) admin siempre puede
    if (hasRole('admin')) { if (DEV) console.groupEnd(); return true }

    // 3) tutor dueño del booking (owner)
    const isOwnerTutor = hasRole('tutor') && !!booking && booking.tutor?.user_id === userId

    // 4) permiso explícito SI HAY PERMISOS; si no, fallback por rol (admin u owner tutor)
    const allowed = canOrRole('booking_appointment.choose_nanny', isOwnerTutor)

    if (DEV) {
      console.table({ isOwnerTutor, allowed })
      console.groupEnd()
    }
    return allowed
  }

  return { canChooseNanny }
}
