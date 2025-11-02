// Mismos valores que el enum de PHP
export enum BookingStatusEnum {
  DRAFT = 'draft',
  PENDING = 'pending',       // Tiene niñera asignada, pero aún no inicia
  CONFIRMED = 'confirmed',   // Confirmado por el sistema o admin
  CANCELLED = 'cancelled',     // Cancelado
  IN_PROGRESS = 'in_progress', // Servicio actualmente en curso
  COMPLETED = 'completed',   // Servicio finalizado
}

// Etiquetas legibles (idénticas a PHP::label())
export const BookingStatusLabels: Record<BookingStatusEnum, string> = {
  [BookingStatusEnum.DRAFT]: 'Borrador',
  [BookingStatusEnum.PENDING]: 'Pendiente',
  [BookingStatusEnum.CONFIRMED]: 'Confirmado',
  [BookingStatusEnum.CANCELLED]: 'Cancelado',
  [BookingStatusEnum.IN_PROGRESS]: 'En curso',
  [BookingStatusEnum.COMPLETED]: 'Finalizado',
};

// -> label()
export function getBookingStatusLabel(status: BookingStatusEnum): string {
  return BookingStatusLabels[status];
}

// -> values()
export const BOOKING_STATUS_VALUES = Object.values(BookingStatusEnum) as BookingStatusEnum[];

// -> labels() (map<string,string>)
export function getBookingStatusLabelsRecord(): Record<string, string> {
  return BOOKING_STATUS_VALUES.reduce((acc, v) => {
    acc[v] = BookingStatusLabels[v];
    return acc;
  }, {} as Record<string, string>);
}

// Buscar etiqueta desde string (útil para datos del backend)
export function getBookingStatusLabelByString(value: string): string {
  const match = (Object.values(BookingStatusEnum) as string[]).find(v => v === value);
  return match ? BookingStatusLabels[match as BookingStatusEnum] : 'Estado desconocido';
}

// Type guard: ¿el string es un BookingStatusEnum válido?
export function isBookingStatus(value: string): value is BookingStatusEnum {
  return (Object.values(BookingStatusEnum) as string[]).includes(value);
}

// Clases para badges de estado (Tailwind + modo oscuro)
export function getBookingStatusBadgeClass(status: BookingStatusEnum | string): string {
  switch (status) {
    case BookingStatusEnum.DRAFT:
      return 'bg-gray-200/70 text-gray-800 dark:bg-gray-600/50 dark:text-gray-200';
    case BookingStatusEnum.PENDING:
      return 'bg-yellow-200/70 text-yellow-700 dark:bg-yellow-400/50 dark:text-yellow-200';
    case BookingStatusEnum.CONFIRMED:
      return 'bg-sky-200/70 text-sky-600 dark:bg-sky-400/50 dark:text-sky-200';
    case BookingStatusEnum.IN_PROGRESS:
      return 'bg-indigo-200/70 text-indigo-700 dark:bg-indigo-400/50 dark:text-indigo-200';
    case BookingStatusEnum.COMPLETED:
      return 'bg-emerald-200/70 text-emerald-600 dark:bg-emerald-400/50 dark:text-emerald-200';
    case BookingStatusEnum.CANCELLED:
      return 'bg-red-200/70 text-red-600 dark:bg-red-400/50 dark:text-red-200';
    default:
      return 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200';
  }
}

// Opciones listas para <Select> o Dropdowns
export const BOOKING_STATUS_OPTIONS = BOOKING_STATUS_VALUES.map(v => ({
  value: v,
  label: BookingStatusLabels[v],
}));
