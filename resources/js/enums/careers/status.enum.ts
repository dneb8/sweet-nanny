// Status Enum - matches quality.enum.ts pattern

// Matching PHP backend enum values
export enum StatusEnum {
  EN_CURSO = 'en_curso',
  FINALIZADO = 'finalizado',
  TITULADO = 'titulado',
  TRUNCA = 'trunca',
}

// Human-readable labels (matching PHP::label())
export const StatusLabels: Record<StatusEnum, string> = {
  [StatusEnum.EN_CURSO]: 'En Curso',
  [StatusEnum.FINALIZADO]: 'Finalizado (Egresado)',
  [StatusEnum.TITULADO]: 'Titulado(a)',
  [StatusEnum.TRUNCA]: 'Trunca (Abandonada)',
};

// Helpers (matching quality.enum.ts pattern)

// -> label()
export function getStatusLabel(s: StatusEnum): string {
  return StatusLabels[s];
}

// -> values()
export const STATUS_VALUES = Object.values(StatusEnum) as StatusEnum[];

// -> labels() (map<string,string>)
export function getStatusLabelsRecord(): Record<string, string> {
  return STATUS_VALUES.reduce((acc, v) => {
    acc[v] = StatusLabels[v];
    return acc;
  }, {} as Record<string, string>);
}

// Get label from string (useful for backend data)
export function getStatusLabelByString(value: string): string {
  const match = (Object.values(StatusEnum) as string[]).find(v => v === value);
  return match ? StatusLabels[match as StatusEnum] : 'Estado desconocido';
}

// Type guard: is the string a valid StatusEnum?
export function isStatus(value: string): value is StatusEnum {
  return (Object.values(StatusEnum) as string[]).includes(value);
}

// Options ready for <Select>
export const STATUS_OPTIONS = STATUS_VALUES.map(v => ({
  value: v,
  label: StatusLabels[v],
}));

// Backward compatibility
export function labels(): Record<StatusEnum, string> {
  return StatusLabels;
}