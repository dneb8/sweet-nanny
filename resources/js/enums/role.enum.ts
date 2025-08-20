export enum RoleEnum {
    ADMIN = 'admin',
    TUTOR = 'tutor',
    NANNY = 'nanny',
}

// Mapeo para versiones legibles
export const RoleLabels: Record<RoleEnum, string> = {
    [RoleEnum.ADMIN]: 'Administrador',
    [RoleEnum.TUTOR]: 'Tutor',
    [RoleEnum.NANNY]: 'Niñera',
};

// Función para obtener la versión legible
export function getRoleLabel(role: RoleEnum): string {
    return RoleLabels[role];
}

// Función para buscar el elemento del enum basado en un string
export function getRoleLabelByString(value: string): string {
    const role = Object.values(RoleEnum).find(enumValue => enumValue === value);

    return role ? RoleLabels[role as RoleEnum] : 'Rol Desconocido';
}
