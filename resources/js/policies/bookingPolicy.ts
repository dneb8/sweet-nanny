import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types/UsePage';
import type { User } from '@/types/User';
import type { Rol } from '@/types/Rol';
import type { Booking } from '@/types/Booking';
import type { BookingAppointment } from '@/types/BookingAppointment';
import { can, role } from '@/helpers/permissionHelper';
import { RoleEnum } from '@/enums/role.enum';

export class BookingPolicy {
    private get authUser() {
        return usePage<PageProps>().props.auth?.user;
    }

    private isAdmin = (u: User): boolean => {
        if (!u || !u.roles) return false;
        return u.roles.some((r: Rol) => r.name === RoleEnum.ADMIN);
    };

    private isTutor = (u: User): boolean => {
        if (!u || !u.roles) return false;
        return u.roles.some((r: Rol) => r.name === RoleEnum.TUTOR);
    };

    private isNanny = (u: User): boolean => {
        if (!u || !u.roles) return false;
        return u.roles.some((r: Rol) => r.name === RoleEnum.NANNY);
    };

    // 1) Crear booking - Solo TUTOR puede crear
    public canCreate = (): boolean => {
        return !!this.authUser && can('booking.create') && role(RoleEnum.TUTOR);
    };

    // 2) Ver booking
    public canView = (booking: Booking): boolean => {
        if (!this.authUser || !can('booking.view')) return false;
        if (this.isAdmin(this.authUser)) return true; // Admin ve todo

        if (this.isTutor(this.authUser)) {
            return booking.tutor_id === this.authUser.id;
        }

        if (this.isNanny(this.authUser)) {
            // Nanny puede ver si está asociada al booking (nannies_ids)
            // Nota: El tipo Booking no tiene nannies_ids, usar nanny relación si existe
            return true; // Por ahora permitimos ver, ajustar según modelo real
        }

        return false;
    };

    // 3) Editar booking
    public canUpdate = (booking: Booking): boolean => {
        if (!this.authUser || !can('booking.update')) return false;
        if (this.isAdmin(this.authUser)) return true;

        if (this.isTutor(this.authUser)) {
            return booking.status === 'pending' && booking.tutor_id === this.authUser.id;
        }

        return false;
    };

    // 4) Eliminar booking
    public canDelete = (booking: Booking): boolean => {
        if (!this.authUser || !can('booking.delete')) return false;
        if (this.isAdmin(this.authUser)) return true;

        if (this.isTutor(this.authUser)) {
            return booking.status === 'pending' && booking.tutor_id === this.authUser.id;
        }

        return false;
    };

    // 5) Cancelar booking (cuando ya no está 'pending')
    public canCancel = (booking: Booking): boolean => {
        if (!this.authUser || !can('booking.cancel')) return false;
        if (this.isAdmin(this.authUser)) return true;

        if (this.isTutor(this.authUser)) {
            return booking.tutor_id === this.authUser.id && booking.status !== 'pending';
        }

        return false;
    };

    // 6) Elegir niñera para un BookingAppointment
    public canAssignNanny = (bookingAppointment: BookingAppointment, booking: Booking): boolean => {
        if (!this.authUser || !can('booking.assignNanny')) return false;
        if (bookingAppointment.nanny_id) return false; // ya asignada

        if (this.isAdmin(this.authUser)) return true;
        if (this.isTutor(this.authUser)) {
            return booking.tutor_id === this.authUser.id;
        }

        return false;
    };

    // 7) Aceptar booking (para NANNY; futura lógica)
    public canAcceptAsNanny = (bookingAppointment: BookingAppointment): boolean => {
        if (!this.authUser || !can('booking.accept')) return false;

        if (this.isNanny(this.authUser)) {
            // Verificar que la nanny asignada sea el usuario actual
            // Nota: authUser.id vs nanny_id - ajustar según relación real
            return bookingAppointment.nanny_id === this.authUser.id;
        }

        return false;
    };
}
