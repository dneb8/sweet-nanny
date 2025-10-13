export enum StatusEnum {
  EN_CURSO = 'en_curso',
  FINALIZADO = 'finalizado',
  TITULADO = 'titulado',
  TRUNCA = 'trunca',
}

export namespace StatusEnum {
    export function labels(): Record<string, string> {
        return {
            [StatusEnum.EN_CURSO]: 'En Curso',
            [StatusEnum.FINALIZADO]: 'Finalizado (Egresado)',
            [StatusEnum.TITULADO]: 'Titulado(a)',
            [StatusEnum.TRUNCA]: 'Trunca (Abandonada)',
        };
    }
}