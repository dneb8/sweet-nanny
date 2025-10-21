// Degree Enum — sincronizado con DegreeEnum.php

export enum DegreeEnum {
  LICENCIATURA = 'licenciatura',
  MAESTRIA = 'maestria',
  DOCTORADO = 'doctorado',
  TECNICO = 'tecnico',
  DIPLOMADO = 'diplomado',
  CERTIFICACION = 'certificacion',
}

// Human-readable labels (matching PHP::label())
export const DegreeLabels: Record<DegreeEnum, string> = {
  [DegreeEnum.LICENCIATURA]: 'Licenciatura',
  [DegreeEnum.MAESTRIA]: 'Maestría',
  [DegreeEnum.DOCTORADO]: 'Doctorado',
  [DegreeEnum.TECNICO]: 'Técnico',
  [DegreeEnum.DIPLOMADO]: 'Diplomado',
  [DegreeEnum.CERTIFICACION]: 'Certificación',
};

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

// Type guard
export function isDegree(value: string): value is DegreeEnum {
  return (Object.values(DegreeEnum) as string[]).includes(value);
}

// Options for <Select>
export const DEGREE_OPTIONS = DEGREE_VALUES.map(v => ({
  value: v,
  label: DegreeLabels[v],
}));

// Backward compatibility helper
export function labels(): Record<DegreeEnum, string> {
  return DegreeLabels;
}
