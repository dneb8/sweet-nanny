import { computed, provide, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Booking } from '@/types/Booking';
import { BookingStatus, getBookingStatusLabel } from '@/enums/booking-status.enum';

export interface FiltrosBooking {
    recurrent: string | null;
    status: string | null;
    date_from: string | null;
    date_to: string | null;
}

export class BookingTableService {
    // Propiedades reactivas del componente
    public bookingEliminar = ref<Booking | null>(null);

    public modalEliminarBooking = ref(false);

    // Filtros reactivos
    public filtros = ref<FiltrosBooking>({
        recurrent: null,
        status: null,
        date_from: null,
        date_to: null,
    });

    // Constante reactiva que contiene las columnas visibles
    public visibleColumns = ref<Array<string>>(['Tutor', 'Dirección', 'Niños', 'Citas', 'Estado', 'Fecha Creación', 'Acciones']);

    public constructor() {
        // Providers para comunicación con DataTable
        provide('bookings_filters', computed(() => this.getFilters()));
        provide('clear_bookings_filters', () => {
            this.filtros.value = {
                recurrent: null,
                status: null,
                date_from: null,
                date_to: null,
            };
        });
    }

    // Getter de filtros
    public getFilters = () => ({
        recurrent: this.filtros.value.recurrent,
        status: this.filtros.value.status,
        date_from: this.filtros.value.date_from,
        date_to: this.filtros.value.date_to,
    });

    // Función para redirigir al detalle de un booking
    public verBooking = (booking: Booking) => {
        router.get(route('bookings.show', booking.id));
    };

    // Función que redirige a la vista para editar booking
    public editarBooking = (booking: Booking) => {
        router.get(route('bookings.edit', booking.id));
    };

    // Función que muestra el modal para eliminar booking
    public abrirModalEliminarBooking = (booking: Booking) => {
        this.bookingEliminar.value = booking;
        this.modalEliminarBooking.value = true;
    };

    // Función que cierra el modal para eliminar booking
    public cerrarModalEliminarBooking = () => {
        this.modalEliminarBooking.value = false;
    };

    // Función para eliminar booking
    public eliminarBooking = () => {
        router.delete(route('bookings.destroy', this.bookingEliminar.value?.id));

        this.modalEliminarBooking.value = false;
    };

    // Helper para obtener el color de un estado
    public getStatusColor = (status: string): string => {
        switch (status) {
            case BookingStatus.PENDIENTE:
                return 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-400/25 dark:text-amber-200 dark:border-amber-400';
            case BookingStatus.EN_ESPERA:
                return 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-400/25 dark:text-yellow-200 dark:border-yellow-400';
            case BookingStatus.APROBADO:
                return 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-400/25 dark:text-emerald-200 dark:border-emerald-400';
            case BookingStatus.EN_CURSO:
                return 'bg-sky-100 text-sky-800 border-sky-200 dark:bg-sky-400/25 dark:text-sky-200 dark:border-sky-400';
            case BookingStatus.FINALIZADO:
                return 'bg-slate-100 text-slate-800 border-slate-200 dark:bg-slate-400/25 dark:text-slate-200 dark:border-slate-400';
            default:
                return 'bg-muted text-muted-foreground';
        }
    };

    // Helper para obtener la etiqueta de un estado
    public getStatusLabel = (status: string): string => {
        return getBookingStatusLabel(status);
    };
}
