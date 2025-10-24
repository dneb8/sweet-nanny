import { PageProps } from '@/types/UsePage';
import { usePage } from '@inertiajs/vue3';

export function can(permissions: string): boolean {
    const page = usePage<PageProps>();
    const permissionsArray = permissions.split('|').map(permission => permission.trim());
    const { permisos : permisosUsuario }  = page.props.auth;

    if (!permisosUsuario) return false;

    for (const permission of permissionsArray) {
        if (permisosUsuario.includes(permission)) return true;
    }

    return false;
}

export function role(roles: string): boolean {
    const page = usePage<PageProps>();
    const rolesArray = roles.split('|').map(role => role.trim());
    const { roles : rolesUsuario }  = page.props.auth;

    if (!rolesUsuario) return false;

    for (const role of rolesArray) {
        if (rolesUsuario.includes(role)) return true;
    }

    return false;
}