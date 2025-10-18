// status.enum.ts
export enum StatusEnum {
  EN_CURSO = 'en_curso',
  FINALIZADO = 'finalizado',
  TITULADO = 'titulado',
  TRUNCA = 'trunca',
}

export function labels(): Record<StatusEnum, string> {
  return {
    [StatusEnum.EN_CURSO]: 'En Curso',
    [StatusEnum.FINALIZADO]: 'Finalizado (Egresado)',
    [StatusEnum.TITULADO]: 'Titulado(a)',
    [StatusEnum.TRUNCA]: 'Trunca (Abandonada)',
  };
}