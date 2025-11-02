export enum CourseNameEnum {
  PRIMEROS_AUXILIOS = 'primeros_auxilios',
  CUIDADO_INFANTIL = 'cuidado_infantil',
  DESARROLLO_INFANTIL = 'desarrollo_infantil',
  NUTRICION_Y_ALIMENTACION = 'nutricion_y_alimentacion',
  EDUCACION_Y_APRENDIZAJE = 'educacion_y_aprendizaje',
  PSICOLOGIA_INFANTIL = 'psicologia_infantil',
  DISCIPLINA_Y_COMPORTAMIENTO = 'disciplina_y_comportamiento',
  LACTANCIA_Y_CUIDADO_BEBES = 'lactancia_y_cuidado_bebes',
  INCLUSION_Y_DIVERSIDAD = 'inclusion_y_diversidad',
  COMUNICACION_Y_LENGUAJE = 'comunicacion_y_lenguaje',
}

export const CourseNameLabels: Record<CourseNameEnum, string> = {
  [CourseNameEnum.PRIMEROS_AUXILIOS]: 'Primeros Auxilios',
  [CourseNameEnum.CUIDADO_INFANTIL]: 'Cuidado Infantil',
  [CourseNameEnum.DESARROLLO_INFANTIL]: 'Desarrollo Infantil',
  [CourseNameEnum.NUTRICION_Y_ALIMENTACION]: 'Nutrición y Alimentación',
  [CourseNameEnum.EDUCACION_Y_APRENDIZAJE]: 'Educación y Aprendizaje',
  [CourseNameEnum.PSICOLOGIA_INFANTIL]: 'Psicología Infantil',
  [CourseNameEnum.DISCIPLINA_Y_COMPORTAMIENTO]: 'Disciplina y Comportamiento',
  [CourseNameEnum.LACTANCIA_Y_CUIDADO_BEBES]: 'Lactancia y Cuidado de Bebés',
  [CourseNameEnum.INCLUSION_Y_DIVERSIDAD]: 'Inclusión y Diversidad',
  [CourseNameEnum.COMUNICACION_Y_LENGUAJE]: 'Comunicación y Lenguaje',
};

export function getCourseNameLabel(c: CourseNameEnum): string {
  return CourseNameLabels[c];
}

export const COURSE_NAME_VALUES = Object.values(CourseNameEnum) as CourseNameEnum[];

export function getCourseNameLabelsRecord(): Record<string, string> {
  return COURSE_NAME_VALUES.reduce((acc, v) => {
    acc[v] = CourseNameLabels[v];
    return acc;
  }, {} as Record<string, string>);
}

export const COURSE_NAME_OPTIONS = COURSE_NAME_VALUES.map(v => ({
  value: v,
  label: CourseNameLabels[v],
}));

export function getCourseNameLabelByString(value: string): string {
  const match = COURSE_NAME_VALUES.find(v => v === value);
  return match ? CourseNameLabels[match] : 'Curso desconocido';
}
