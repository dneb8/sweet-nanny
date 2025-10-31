import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types/UsePage';
import type { User } from '@/types/User';
import { can } from '@/helpers/permissionHelper';

class UserPolicy {
    private authUser = usePage<PageProps>().props.auth.user;

    private isAdmin(): boolean {
        return this.authUser.roles?.includes('admin');
    }

    // Solo admin puede actualizar usuarios.
    canUpdate(): boolean {
        if (!can('user.update')) return false;
        if (!this.isAdmin()) return false;
        return true; // Admin puede editar a cualquiera (incluido él mismo)
    }

    // Solo admin puede eliminar usuarios, pero no a sí mismo.
    canDelete(user: User): boolean {
        if (!can('user.delete')) return false;
        if (!this.isAdmin()) return false;
        if (this.authUser.id === user.ulid) return false; // No puede eliminarse a sí mismo
        return true;
    }
}

export const userPolicy = new UserPolicy();
