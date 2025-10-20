// Course Name Enum - matches quality.enum.ts pattern

// Matching PHP backend enum values
export enum CourseNameEnum {
  PRIMEROS_AUXILIOS = 'primeros_auxilios',
  RCP_PEDIATRICO = 'rcp_pediatrico',
  ALIMENTACION_COMPLEMENTARIA = 'alimentacion_complementaria',
  DISCIPLINA_POSITIVA = 'disciplina_positiva',
  AUTISMO_TEA = 'autismo_tea',
  LECTOESCRITURA = 'lectoescritura',
  CUIDADO_RECIEN_NACIDO = 'cuidado_recien_nacido',
  ESTIMULACION_MOTRIZ = 'estimulacion_motriz',
  CUIDADO_INFANTIL = 'cuidado_infantil',
  NUTRICION_INFANTIL = 'nutricion_infantil',
  DESARROLLO_INFANTIL = 'desarrollo_infantil',
  PSICOLOGIA_INFANTIL = 'psicologia_infantil',
  PEDAGOGIA = 'pedagogia',
  EDUCACION_ESPECIAL = 'educacion_especial',
  LACTANCIA_MATERNA = 'lactancia_materna',
  ESTIMULACION_TEMPRANA = 'estimulacion_temprana',
  MANEJO_CONDUCTUAL = 'manejo_conductual',
}

// Human-readable labels (matching PHP::label())
export const CourseNameLabels: Record<CourseNameEnum, string> = {
  [CourseNameEnum.PRIMEROS_AUXILIOS]: 'Primeros Auxilios',
  [CourseNameEnum.RCP_PEDIATRICO]: 'RCP Pediátrico',
  [CourseNameEnum.ALIMENTACION_COMPLEMENTARIA]: 'Alimentación Complementaria',
  [CourseNameEnum.DISCIPLINA_POSITIVA]: 'Disciplina Positiva',
  [CourseNameEnum.AUTISMO_TEA]: 'Autismo (TEA)',
  [CourseNameEnum.LECTOESCRITURA]: 'Lectoescritura',
  [CourseNameEnum.CUIDADO_RECIEN_NACIDO]: 'Cuidado de Recién Nacido',
  [CourseNameEnum.ESTIMULACION_MOTRIZ]: 'Estimulación Motriz',
  [CourseNameEnum.CUIDADO_INFANTIL]: 'Cuidado Infantil',
  [CourseNameEnum.NUTRICION_INFANTIL]: 'Nutrición Infantil',
  [CourseNameEnum.DESARROLLO_INFANTIL]: 'Desarrollo Infantil',
  [CourseNameEnum.PSICOLOGIA_INFANTIL]: 'Psicología Infantil',
  [CourseNameEnum.PEDAGOGIA]: 'Pedagogía',
  [CourseNameEnum.EDUCACION_ESPECIAL]: 'Educación Especial',
  [CourseNameEnum.LACTANCIA_MATERNA]: 'Lactancia Materna',
  [CourseNameEnum.ESTIMULACION_TEMPRANA]: 'Estimulación Temprana',
  [CourseNameEnum.MANEJO_CONDUCTUAL]: 'Manejo Conductual',
};

// Helpers (matching quality.enum.ts pattern)

// -> label()
export function getCourseNameLabel(c: CourseNameEnum): string {
  return CourseNameLabels[c];
}

// -> values()
export const COURSE_NAME_VALUES = Object.values(CourseNameEnum) as CourseNameEnum[];

// -> labels() (map<string,string>)
export function getCourseNameLabelsRecord(): Record<string, string> {
  return COURSE_NAME_VALUES.reduce((acc, v) => {
    acc[v] = CourseNameLabels[v];
    return acc;
  }, {} as Record<string, string>);
}

// Get label from string (useful for backend data)
export function getCourseNameLabelByString(value: string): string {
  const match = (Object.values(CourseNameEnum) as string[]).find(v => v === value);
  return match ? CourseNameLabels[match as CourseNameEnum] : 'Curso desconocido';
}

// Type guard: is the string a valid CourseNameEnum?
export function isCourseName(value: string): value is CourseNameEnum {
  return (Object.values(CourseNameEnum) as string[]).includes(value);
}

// Options ready for <Select>
export const COURSE_NAME_OPTIONS = COURSE_NAME_VALUES.map(v => ({
  value: v,
  label: CourseNameLabels[v],
}));
