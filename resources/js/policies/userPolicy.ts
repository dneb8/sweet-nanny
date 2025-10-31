import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types/UsePage';
import type { User } from '@/types/User';
import { can, hasRole } from '@/helpers/permissionHelper';

class UserPolicy {
    private authUser = usePage<PageProps>().props.auth.user;

    // Solo admin puede actualizar usuarios.
    canUpdate(user: User): boolean {
        if (!can('user.update')) return false;
        if (!hasRole('admin')) return false;
        return true; // Admin puede editar a cualquiera (incluido él mismo)
    }

    // Solo admin puede eliminar usuarios, pero no a sí mismo.
    canDelete(user: User): boolean {
        if (!can('user.delete')) return false;
        if (!hasRole('admin')) return false;
        if (this.authUser.id === user.ulid) return false; // No puede eliminarse a sí mismo
        return true;
    }
}

export const userPolicy = new UserPolicy();
