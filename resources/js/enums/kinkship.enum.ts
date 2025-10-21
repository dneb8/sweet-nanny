// types/Kinship.ts

// Mismos valores que el enum de PHP
export enum KinshipEnum {
  HIJO = 'hijo',
  SOBRINO = 'sobrino',
  PRIMO = 'primo',
  HERMANO = 'hermano',
  OTRO = 'otro',
}

// Etiquetas legibles (idénticas a PHP::label())
export const KinshipLabels: Record<KinshipEnum, string> = {
  [KinshipEnum.HIJO]: 'Hijo(a)',
  [KinshipEnum.SOBRINO]: 'Sobrino(a)',
  [KinshipEnum.PRIMO]: 'Primo(a)',
  [KinshipEnum.HERMANO]: 'Hermano(a)',
  [KinshipEnum.OTRO]: 'Otro',
};

// Helpers equivalentes a los métodos de PHP

// -> label()
export function getKinshipLabel(k: KinshipEnum): string {
  return KinshipLabels[k];
}

// -> values()
export const KINSHIP_VALUES = Object.values(KinshipEnum) as KinshipEnum[];

// -> labels() (map<string,string>)
export function getKinshipLabelsRecord(): Record<string, string> {
  return KINSHIP_VALUES.reduce((acc, v) => {
    acc[v] = KinshipLabels[v];
    return acc;
  }, {} as Record<string, string>);
}

// Buscar etiqueta desde string (útil para datos que vienen del backend)
export function getKinshipLabelByString(value: string): string {
  const match = (Object.values(KinshipEnum) as string[]).find(v => v === value);
  return match ? KinshipLabels[match as KinshipEnum] : 'Parentesco desconocido';
}

// Type guard: ¿el string es un KinshipEnum válido?
export function isKinship(value: string): value is KinshipEnum {
  return (Object.values(KinshipEnum) as string[]).includes(value);
}

// Opciones listas para <Select>
export const KINSHIP_OPTIONS = KINSHIP_VALUES.map(v => ({
  value: v,
  label: KinshipLabels[v],
}));
