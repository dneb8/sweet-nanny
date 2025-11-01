// Career Name Enum - matches quality.enum.ts pattern

// Matching PHP backend enum values
export enum CareerNameEnum {
  PEDAGOGIA = 'pedagogia',
  PSICOLOGIA = 'psicologia',
  ENFERMERIA = 'enfermeria',
  DOCENCIA = 'docencia',
  NUTRICION = 'nutricion',
  TRABAJO_SOCIAL = 'trabajo_social',
  PSICOPEDAGOGIA = 'psicopedagogia',
  TERAPIA_PSICOMOTRIZ = 'terapia_psicomotriz',
  PEDIATRIA = 'pediatria',
  ARTES_ESCENICAS_DANZA = 'artes_escenicas_danza',
}

// Human-readable labels (matching PHP::label())
export const CareerNameLabels: Record<CareerNameEnum, string> = {
  [CareerNameEnum.PEDAGOGIA]: 'Pedagogía',
  [CareerNameEnum.PSICOLOGIA]: 'Psicología',
  [CareerNameEnum.ENFERMERIA]: 'Enfermería',
  [CareerNameEnum.DOCENCIA]: 'Docencia',
  [CareerNameEnum.NUTRICION]: 'Nutrición',
  [CareerNameEnum.TRABAJO_SOCIAL]: 'Trabajo Social',
  [CareerNameEnum.PSICOPEDAGOGIA]: 'Psicopedagogía',
  [CareerNameEnum.TERAPIA_PSICOMOTRIZ]: 'Terapia Psicomotriz',
  [CareerNameEnum.PEDIATRIA]: 'Pediatría',
  [CareerNameEnum.ARTES_ESCENICAS_DANZA]: 'Artes Escénicas / Danza',
};

// Helpers (matching quality.enum.ts pattern)

// -> label()
export function getCareerNameLabel(c: CareerNameEnum): string {
  return CareerNameLabels[c];
}

// -> values()
export const CAREER_NAME_VALUES = Object.values(CareerNameEnum) as CareerNameEnum[];

// -> labels() (map<string,string>)
export function getCareerNameLabelsRecord(): Record<string, string> {
  return CAREER_NAME_VALUES.reduce((acc, v) => {
    acc[v] = CareerNameLabels[v];
    return acc;
  }, {} as Record<string, string>);
}

// Get label from string (useful for backend data)
export function getCareerNameLabelByString(value: string): string {
  const match = (Object.values(CareerNameEnum) as string[]).find(v => v === value);
  return match ? CareerNameLabels[match as CareerNameEnum] : 'Carrera desconocida';
}

// Type guard: is the string a valid CareerNameEnum?
export function isCareerName(value: string): value is CareerNameEnum {
  return (Object.values(CareerNameEnum) as string[]).includes(value);
}

// Options ready for <Select>
export const CAREER_NAME_OPTIONS = CAREER_NAME_VALUES.map(v => ({
  value: v,
  label: CareerNameLabels[v],
}));

// Backward compatibility
export const NameCareerEnum = CareerNameEnum;
export function labels(): Record<CareerNameEnum, string> {
  return CareerNameLabels;
}
