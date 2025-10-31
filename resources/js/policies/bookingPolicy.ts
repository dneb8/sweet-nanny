import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types/UsePage';
import type { Booking } from '@/types/Booking';
import { can, hasRole } from '@/helpers/permissionHelper';

class BookingPolicy {
    private authUser = usePage<PageProps>().props.auth.user;

    // Solo tutor puede crear bookings
    canCreate(): boolean {
        if (!can('bookings.create')) return false;
        if (!hasRole('tutor')) return false;
        return true;
    }

    // Tutor puede editar solo si el booking está en estado pending
    // Admin puede editar siempre
    canUpdate(booking: Booking): boolean {
        if (hasRole('admin')) {
            return can('bookings.update');
        }

        if (hasRole('tutor')) {
            if (!can('bookings.update')) return false;
            // Solo puede editar si está en pending
            return booking.status === 'pending';
        }

        return false;
    }

    // Tutor puede eliminar solo si el booking está en estado pending
    // Admin puede eliminar siempre
    canDelete(booking: Booking): boolean {
        if (hasRole('admin')) {
            return can('bookings.delete');
        }

        if (hasRole('tutor')) {
            if (!can('bookings.delete')) return false;
            // Solo puede eliminar si está en pending
            return booking.status === 'pending';
        }

        return false;
    }

    // Tutor puede cancelar si ya no está pending
    // Admin puede cancelar siempre
    canCancel(booking: Booking): boolean {
        if (hasRole('admin')) {
            return can('bookings.cancel');
        }

        if (hasRole('tutor')) {
            if (!can('bookings.cancel')) return false;
            // Puede cancelar si NO está en pending (ya confirmado o en proceso)
            return booking.status !== 'pending' && booking.status !== 'cancelled';
        }

        return false;
    }

    // Admin puede ver todo
    // Tutor puede ver sus propios bookings
    // Nanny puede ver detalles de bookings
    canView(booking: Booking): boolean {
        if (hasRole('admin')) {
            return can('bookings.view');
        }

        if (hasRole('tutor')) {
            return can('bookings.view');
        }

        if (hasRole('nanny')) {
            return can('bookings.view');
        }

        return false;
    }

    // Nanny puede aceptar solo si el booking está pending
    canAccept(booking: Booking): boolean {
        if (!hasRole('nanny')) return false;
        if (!can('bookings.accept')) return false;
        // Solo puede aceptar si está en pending
        return booking.status === 'pending';
    }
}

export const bookingPolicy = new BookingPolicy();
