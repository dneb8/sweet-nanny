export enum NameCareerEnum {
  PEDAGOGIA = "PEDAGOGIA",
  PSICOLOGIA = "PSICOLOGIA",
  ENFERMERIA = "ENFERMERIA",
  ARTES = "ARTES",
  EDUCACION_ESPECIAL = "EDUCACION_ESPECIAL",
  TRABAJO_SOCIAL = "TRABAJO_SOCIAL",
  PSICOPEDAGOGIA = "PSICOPEDAGOGIA",
  EDUCACION_INFANTIL = "EDUCACION_INFANTIL",
  NUTRICION = "NUTRICION"
}

export namespace NameCareerEnum {
  export function labels(): Record<string, string> {
    return {
      [NameCareerEnum.PEDAGOGIA]: 'Pedagogía',
      [NameCareerEnum.PSICOLOGIA]: 'Psicología',
      [NameCareerEnum.ENFERMERIA]: 'Enfermería',
      [NameCareerEnum.ARTES]: 'Artes',
      [NameCareerEnum.EDUCACION_ESPECIAL]: 'Educación especial',
      [NameCareerEnum.TRABAJO_SOCIAL]: 'Trabajo social',
      [NameCareerEnum.PSICOPEDAGOGIA]: 'Psicopedagogía',
      [NameCareerEnum.EDUCACION_INFANTIL]: 'Educación infantil',
      [NameCareerEnum.NUTRICION]: 'Nutrición'
    }
  }
}
