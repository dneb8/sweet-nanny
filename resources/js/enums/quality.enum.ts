// types/Quality.ts

// Mismos valores que el enum de PHP
export enum QualityEnum {
  RESPONSABLE = 'responsable',
  PACIENTE = 'paciente',
  CREATIVA = 'creativa',
  PUNTUAL = 'puntual',
  CARIÑOSA = 'carinosa',
  ORGANIZADA = 'organizada',
  COMUNICATIVA = 'comunicativa',
  EXTROVERTIDA = 'extrovertida',
  LECTORA = 'lectora',
  EMPATICA = 'empatica',
  CUIDADOSA = 'cuidadosa',
  HONESTA = 'honesta',
  ATENTA = 'atenta',
  ASERTIVA = 'asertiva',
  RESPETUOSA = 'respetuosa',
  DINAMICA = 'dinamica',
  PROACTIVA = 'proactiva',
  DISCRETA = 'discreta',
  OBSERVADORA = 'observadora',
  RESOLUTIVA = 'resolutiva',
  COLABORATIVA = 'colaborativa',
  FLEXIBLE = 'flexible',
  LUDICA = 'ludica',
  ADAPTABLE = 'adaptable',
  PERSEVERANTE = 'perseverante',
  TOLERANTE = 'tolerante',
  SEGURA = 'segura',
  BILINGUE = 'bilingue',
}

// Etiquetas legibles (idénticas a PHP::label())
export const QualityLabels: Record<QualityEnum, string> = {
  [QualityEnum.RESPONSABLE]: 'Responsable',
  [QualityEnum.PACIENTE]: 'Paciente',
  [QualityEnum.CREATIVA]: 'Creativa',
  [QualityEnum.PUNTUAL]: 'Puntual',
  [QualityEnum.CARIÑOSA]: 'Cariñosa',
  [QualityEnum.ORGANIZADA]: 'Organizada',
  [QualityEnum.COMUNICATIVA]: 'Comunicativa',
  [QualityEnum.EXTROVERTIDA]: 'Extrovertida',
  [QualityEnum.LECTORA]: 'Lectora',
  [QualityEnum.EMPATICA]: 'Empática',
  [QualityEnum.CUIDADOSA]: 'Cuidadosa',
  [QualityEnum.HONESTA]: 'Honesta',
  [QualityEnum.ATENTA]: 'Atenta',
  [QualityEnum.ASERTIVA]: 'Asertiva',
  [QualityEnum.RESPETUOSA]: 'Respetuosa',
  [QualityEnum.DINAMICA]: 'Dinámica',
  [QualityEnum.PROACTIVA]: 'Proactiva',
  [QualityEnum.DISCRETA]: 'Discreta',
  [QualityEnum.OBSERVADORA]: 'Observadora',
  [QualityEnum.RESOLUTIVA]: 'Resolutiva',
  [QualityEnum.COLABORATIVA]: 'Colaborativa',
  [QualityEnum.FLEXIBLE]: 'Flexible',
  [QualityEnum.LUDICA]: 'Lúdica',
  [QualityEnum.ADAPTABLE]: 'Adaptable',
  [QualityEnum.PERSEVERANTE]: 'Perseverante',
  [QualityEnum.TOLERANTE]: 'Tolerante',
  [QualityEnum.SEGURA]: 'Segura',
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
