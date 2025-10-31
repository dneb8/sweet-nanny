import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types/UsePage';
import type { BookingAppointment } from '@/types/BookingAppointment';
import type { Booking } from '@/types/Booking';
import { can, hasRole } from '@/helpers/permissionHelper';

class BookingAppointmentPolicy {
    private authUser = usePage<PageProps>().props.auth.user;

    /**
     * Check if user can choose a nanny for an appointment
     */
    canChooseNanny(appointment: BookingAppointment, booking?: Booking): boolean {
        // Check permission
        if (!can('booking_appointment.choose_nanny')) return false;

        // Appointment must not have a nanny already assigned
        if (appointment.nanny_id !== null && appointment.nanny_id !== undefined) return false;

        // Admin can choose nanny for any appointment
        if (hasRole('admin')) return true;

        // Tutor can only choose for their own bookings
        if (hasRole('tutor') && booking) {
            return booking.tutor?.user_id === this.authUser.id;
        }

        return false;
    }
}

export const bookingAppointmentPolicy = new BookingAppointmentPolicy();
