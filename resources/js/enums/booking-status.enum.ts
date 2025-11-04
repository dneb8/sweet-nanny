export enum BookingStatus {
    PENDIENTE = 'pendiente',
    EN_ESPERA = 'en_espera',
    APROBADO = 'aprobado',
    EN_CURSO = 'en_curso',
    FINALIZADO = 'finalizado',
}

export const BookingStatusLabels: Record<BookingStatus, string> = {
    [BookingStatus.PENDIENTE]: 'Pendiente',
    [BookingStatus.EN_ESPERA]: 'En Espera',
    [BookingStatus.APROBADO]: 'Aprobado',
    [BookingStatus.EN_CURSO]: 'En Curso',
    [BookingStatus.FINALIZADO]: 'Finalizado',
};

export function getBookingStatusLabel(status: string): string {
    return BookingStatusLabels[status as BookingStatus] || status;
}
