import { usePage } from '@inertiajs/vue3'
import type { PageProps } from '@/types/UsePage'

export function getRoles(): string[] {
  const user = usePage<PageProps>().props.auth?.user as any
  const roles = user?.roles ?? []
  return Array.isArray(roles)
    ? roles.map(r => (typeof r === 'string' ? r : r?.name)).filter(Boolean) as string[]
    : []
}

export function getPermissions(): string[] {
  const user = usePage<PageProps>().props.auth?.user as any
  const perms = user?.permissions ?? []
  return Array.isArray(perms)
    ? perms.map(p => (typeof p === 'string' ? p : p?.name)).filter(Boolean) as string[]
    : []
}

export function hasRole(roleName: string): boolean {
  return getRoles().includes(roleName)
}

// Alias for hasRole to maintain backward compatibility
export const role = hasRole

export function can(permissionName: string): boolean {
  return getPermissions().includes(permissionName)
}

/**
 * Si NO hay permisos cargados en el frontend, usa un fallback (por rol).
 * Si sí hay permisos, respeta el permiso explícito.
 */
export function canOrRole(permissionName: string, roleFallback: boolean): boolean {
  const perms = getPermissions()
  if (perms.length === 0) return roleFallback
  return perms.includes(permissionName)
}
