// Degree Enum - matches quality.enum.ts pattern

// Matching PHP backend enum values
export enum DegreeEnum {
  BACHILLERATO = 'bachillerato',
  TECNICO = 'tecnico',
  LICENCIATURA = 'licenciatura',
  MAESTRIA = 'maestria',
  DOCTORADO = 'doctorado',
}

// Human-readable labels (matching PHP::label())
export const DegreeLabels: Record<DegreeEnum, string> = {
  [DegreeEnum.BACHILLERATO]: 'Bachillerato / Preparatoria',
  [DegreeEnum.TECNICO]: 'Técnico / Carrera Técnica',
  [DegreeEnum.LICENCIATURA]: 'Licenciatura / Ingeniería',
  [DegreeEnum.MAESTRIA]: 'Maestría / Especialidad',
  [DegreeEnum.DOCTORADO]: 'Doctorado',
};

// Helpers (matching quality.enum.ts pattern)

// -> label()
export function getDegreeLabel(d: DegreeEnum): string {
  return DegreeLabels[d];
}

// -> values()
export const DEGREE_VALUES = Object.values(DegreeEnum) as DegreeEnum[];

// -> labels() (map<string,string>)
export function getDegreeLabelsRecord(): Record<string, string> {
  return DEGREE_VALUES.reduce((acc, v) => {
    acc[v] = DegreeLabels[v];
    return acc;
  }, {} as Record<string, string>);
}

// Get label from string (useful for backend data)
export function getDegreeLabelByString(value: string): string {
  const match = (Object.values(DegreeEnum) as string[]).find(v => v === value);
  return match ? DegreeLabels[match as DegreeEnum] : 'Grado desconocido';
}

// Type guard: is the string a valid DegreeEnum?
export function isDegree(value: string): value is DegreeEnum {
  return (Object.values(DegreeEnum) as string[]).includes(value);
}

// Options ready for <Select>
export const DEGREE_OPTIONS = DEGREE_VALUES.map(v => ({
  value: v,
  label: DegreeLabels[v],
}));

// Backward compatibility
export function labels(): Record<DegreeEnum, string> {
  return DegreeLabels;
}
