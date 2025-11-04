import { computed, provide, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { BookingAppointment } from '@/types/BookingAppointment';
import { BookingStatus, getBookingStatusLabel } from '@/enums/booking-status.enum';
import { User } from '@/types/User';

export interface FiltrosBookingAppointment {
    status: string | null;
}

export class BookingAppointmentTableService {
    // Filtros reactivos
    public filtros = ref<FiltrosBookingAppointment>({
        status: null,
    });

    // Constante reactiva que contiene las columnas visibles
    public visibleColumns = ref<Array<string>>([
        'Tutor',
        'Niños',
        'Servicio',
        'Zona',
        'Status',
        'Start Date',
        'End Date',
        'Niñera',
        'Acciones',
    ]);

    public constructor() {
        // Providers for DataTable communication
        provide('booking_appointments_filters', computed(() => this.getFilters()));
        provide('clear_booking_appointments_filters', () => {
            this.filtros.value = {
                status: null,
            };
        });
    }

    // Getter de filtros
    public getFilters = () => ({
        status: this.filtros.value.status,
    });

    // Función para redirigir al perfil de usuario
    public verUsuarioPerfil = (user: User | undefined) => {
        if (!user?.ulid) return;
        router.get(route('users.show', user.ulid));
    };

    // Función para redirigir al detalle del servicio (booking)
    public verBooking = (bookingId: number) => {
        router.get(route('bookings.show', bookingId));
    };

    // Helper para obtener el color de un estado
    public getStatusColor = (status: string): string => {
        switch (status) {
            case BookingStatus.PENDIENTE:
            case 'pending':
            case 'draft':
                return 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-400/25 dark:text-amber-200 dark:border-amber-400';
            case BookingStatus.EN_ESPERA:
                return 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-400/25 dark:text-yellow-200 dark:border-yellow-400';
            case BookingStatus.APROBADO:
            case 'confirmed':
                return 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-400/25 dark:text-emerald-200 dark:border-emerald-400';
            case BookingStatus.EN_CURSO:
                return 'bg-sky-100 text-sky-800 border-sky-200 dark:bg-sky-400/25 dark:text-sky-200 dark:border-sky-400';
            case BookingStatus.FINALIZADO:
            case 'completed':
                return 'bg-slate-100 text-slate-800 border-slate-200 dark:bg-slate-400/25 dark:text-slate-200 dark:border-slate-400';
            case 'cancelled':
                return 'bg-rose-100 text-rose-800 border-rose-200 dark:bg-rose-400/25 dark:text-rose-200 dark:border-rose-400';
            default:
                return 'bg-muted text-muted-foreground';
        }
    };

    // Helper para obtener la etiqueta de un estado
    public getStatusLabel = (status: string): string => {
        const statusLabels: Record<string, string> = {
            pending: 'Pendiente',
            draft: 'Borrador',
            confirmed: 'Confirmado',
            completed: 'Completado',
            cancelled: 'Cancelado',
            en_espera: 'En Espera',
            aprobado: 'Aprobado',
            en_curso: 'En Curso',
            finalizado: 'Finalizado',
        };
        return statusLabels[status] || getBookingStatusLabel(status) || status;
    };
}
