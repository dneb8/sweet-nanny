import { computed, provide, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Booking } from '@/types/Booking';

export class BookingTableService {
    // Propiedades reactivas del componente
    public bookingEliminar = ref<Booking | null>(null);

    public modalEliminarBooking = ref(false);

    // Constante reactiva que contiene las columnas visibles
    public visibleColumns = ref<Array<string>>(['Tutor', 'Dirección', 'Niños', 'Citas', 'Fecha Creación', 'Acciones']);

    public constructor() {
        // Providers para comunicación con DataTable
        provide('bookings_filters', computed(() => this.getFilters()));
        provide('clear_bookings_filters', () => {
            // No filters for now
        });
    }

    // Getter de filtros
    public getFilters = () => ({
        // No filters for now
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
}
