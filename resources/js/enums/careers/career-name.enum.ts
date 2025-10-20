// Career Name Enum - matches quality.enum.ts pattern

// Matching PHP backend enum values
export enum CareerNameEnum {
  PEDAGOGIA = 'pedagogia',
  EDUCACION_PREESCOLAR = 'educacion_preescolar',
  PSICOLOGIA_INFANTIL = 'psicologia_infantil',
  EDUCACION_ESPECIAL = 'educacion_especial',
  PUERICULTURA = 'puericultura',
  TERAPIA_OCUPACIONAL = 'terapia_ocupacional',
  ESTIMULACION_TEMPRANA = 'estimulacion_temprana',
  ENFERMERIA_PEDIATRICA = 'enfermeria_pediatrica',
  PSICOLOGIA = 'psicologia',
  ENFERMERIA = 'enfermeria',
  ARTES = 'artes',
  TRABAJO_SOCIAL = 'trabajo_social',
  PSICOPEDAGOGIA = 'psicopedagogia',
  EDUCACION_INFANTIL = 'educacion_infantil',
  NUTRICION = 'nutricion',
}

// Human-readable labels (matching PHP::label())
export const CareerNameLabels: Record<CareerNameEnum, string> = {
  [CareerNameEnum.PEDAGOGIA]: 'Pedagogía',
  [CareerNameEnum.EDUCACION_PREESCOLAR]: 'Educación Preescolar',
  [CareerNameEnum.PSICOLOGIA_INFANTIL]: 'Psicología Infantil',
  [CareerNameEnum.EDUCACION_ESPECIAL]: 'Educación Especial',
  [CareerNameEnum.PUERICULTURA]: 'Puericultura',
  [CareerNameEnum.TERAPIA_OCUPACIONAL]: 'Terapia Ocupacional',
  [CareerNameEnum.ESTIMULACION_TEMPRANA]: 'Estimulación Temprana',
  [CareerNameEnum.ENFERMERIA_PEDIATRICA]: 'Enfermería Pediátrica',
  [CareerNameEnum.PSICOLOGIA]: 'Psicología',
  [CareerNameEnum.ENFERMERIA]: 'Enfermería',
  [CareerNameEnum.ARTES]: 'Artes',
  [CareerNameEnum.TRABAJO_SOCIAL]: 'Trabajo Social',
  [CareerNameEnum.PSICOPEDAGOGIA]: 'Psicopedagogía',
  [CareerNameEnum.EDUCACION_INFANTIL]: 'Educación Infantil',
  [CareerNameEnum.NUTRICION]: 'Nutrición',
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
