// degree.enum.ts
export enum DegreeEnum {
  BACHILLERATO = 'bachillerato',
  TECNICO = 'tecnico',
  LICENCIATURA = 'licenciatura',
  MAESTRIA = 'maestria',
  DOCTORADO = 'doctorado',
}

export function labels(): Record<DegreeEnum, string> {
  return {
    [DegreeEnum.BACHILLERATO]: 'Bachillerato / Preparatoria',
    [DegreeEnum.TECNICO]: 'Técnico / Carrera Técnica',
    [DegreeEnum.LICENCIATURA]: 'Licenciatura / Ingeniería',
    [DegreeEnum.MAESTRIA]: 'Maestría / Especialidad',
    [DegreeEnum.DOCTORADO]: 'Doctorado',
  };
}
