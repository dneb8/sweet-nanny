// types/Quality.ts

// Mismos valores que el enum de PHP
export enum QualityEnum {
  EMPATICA = 'empatica',
  CREATIVA = 'creativa',
  PACIENTE = 'paciente',
  CARIÑOSA = 'carinosa',
  OBSERVADORA = 'observadora',
  ASERTIVA = 'asertiva',
  PROACTIVA = 'proactiva',
  FLEXIBLE = 'flexible',
  LUDICA = 'ludica',
  BILINGUE = 'bilingue',
}

// Etiquetas legibles (idénticas a PHP::label())
export const QualityLabels: Record<QualityEnum, string> = {
  [QualityEnum.EMPATICA]: 'Empática',
  [QualityEnum.CREATIVA]: 'Creativa',
  [QualityEnum.PACIENTE]: 'Paciente',
  [QualityEnum.CARIÑOSA]: 'Cariñosa',
  [QualityEnum.OBSERVADORA]: 'Observadora',
  [QualityEnum.ASERTIVA]: 'Asertiva',
  [QualityEnum.PROACTIVA]: 'Proactiva',
  [QualityEnum.FLEXIBLE]: 'Flexible',
  [QualityEnum.LUDICA]: 'Lúdica',
  [QualityEnum.BILINGUE]: 'Bilingüe',
};

// Helpers equivalentes a los métodos de PHP

// -> label()
export function getQualityLabel(q: QualityEnum): string {
  return QualityLabels[q];
}

// -> values()
export const QUALITY_VALUES = Object.values(QualityEnum) as QualityEnum[];

// -> labels() (map<string,string>)
export function getQualityLabelsRecord(): Record<string, string> {
  return QUALITY_VALUES.reduce((acc, v) => {
    acc[v] = QualityLabels[v];
    return acc;
  }, {} as Record<string, string>);
}

// Buscar etiqueta desde string (útil para datos que vienen del backend)
export function getQualityLabelByString(value: string): string {
  const match = (Object.values(QualityEnum) as string[]).find(v => v === value);
  return match ? QualityLabels[match as QualityEnum] : 'Cualidad desconocida';
}

// Type guard: ¿el string es un QualityEnum válido?
export function isQuality(value: string): value is QualityEnum {
  return (Object.values(QualityEnum) as string[]).includes(value);
}

// Opciones listas para <Select>
export const QUALITY_OPTIONS = QUALITY_VALUES.map(v => ({
  value: v,
  label: QualityLabels[v],
}));

// Compatibilidad con código existente
export const qualitiesList = QUALITY_VALUES;
