import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types/UsePage';

export type AppRole = 'admin' | 'tutor' | 'nanny';

const toArray = (v: string | string[]) => (Array.isArray(v) ? v : v.split('|').map((s) => s.trim()).filter(Boolean));

export function can(permissions: string | string[]): boolean {
    const page = usePage<PageProps>();
    const perms = page.props.auth?.permisos ?? [];
    return toArray(permissions).some((p) => perms.includes(p));
}

export function hasRole(roles: AppRole | AppRole[]): boolean {
    const page = usePage<PageProps>();
    const userRoles = (page.props.auth?.roles ?? []) as AppRole[];
    return toArray(roles as string | string[]).some((r) => userRoles.includes(r as AppRole));
}