/**
 * Frontend permissions mapping
 * This should be kept in sync with backend permissions (app/Enums/Permissions/)
 */

export const Permissions = {
    user: {
        index: ['admin'],
        create: ['admin'],
        update: ['admin'],
        delete: ['admin'],
    },
} as const;

export type PermissionMap = typeof Permissions;
export type ResourceKey = keyof PermissionMap;
export type ActionKey<T extends ResourceKey> = keyof PermissionMap[T];
export type Role = 'admin' | 'nanny' | 'tutor';

/**
 * Check if user has permission for a specific action
 */
export function hasPermission(userRoles: string[], resource: ResourceKey, action: string): boolean {
    const allowedRoles = (Permissions[resource] as Record<string, readonly string[]>)[action];
    if (!allowedRoles) return false;
    return userRoles.some((role) => allowedRoles.includes(role.toLowerCase()));
}
