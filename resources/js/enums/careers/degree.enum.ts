export enum DegreeEnum {
    BACHILLERATO = 'bachillerato',
    TECNICO = 'tecnico',
    LICENCIATURA = 'licenciatura',
    MAESTRIA = 'maestria',
    DOCTORADO = 'doctorado',
}

export namespace DegreeEnum {
    /**
     * Devuelve el mapa de valores de la DB a etiquetas amigables para el frontend.
     */
    export function labels(): Record<string, string> {
        return {
            [DegreeEnum.BACHILLERATO]: 'Bachillerato / Preparatoria',
            [DegreeEnum.TECNICO]: 'Técnico / Carrera Técnica',
            [DegreeEnum.LICENCIATURA]: 'Licenciatura / Ingeniería',
            [DegreeEnum.MAESTRIA]: 'Maestría / Especialidad',
            [DegreeEnum.DOCTORADO]: 'Doctorado',
        };
    }
}
