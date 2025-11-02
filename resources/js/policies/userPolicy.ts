import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types/UsePage';
import type { User } from '@/types/User';
import type { Rol } from '@/types/Rol';
import { can, role } from '@/helpers/permissionHelper';
import { RoleEnum } from '@/enums/role.enum';

class UserPolicy {
    private get authUser() {
        return usePage<PageProps>().props.auth?.user;
    }

    // Helper para determinar si un usuario es admin
    private isAdmin = (user: User): boolean => {
        if (!user || !user.roles) return false;
        return user.roles.some((r: Rol) => r.name === RoleEnum.ADMIN);
    };

    // Helper para determinar si un usuario es tutor
    private isTutor = (user: User): boolean => {
        if (!user || !user.roles) return false;
        return user.roles.some((r: Rol) => r.name === RoleEnum.TUTOR);
    };

    // Helper para determinar si un usuario es nanny
    private isNanny = (user: User): boolean => {
        if (!user || !user.roles) return false;
        return user.roles.some((r: Rol) => r.name === RoleEnum.NANNY);
    };

    // Solo admin puede actualizar usuarios
    public canUpdateUser = (user: User): boolean => {
        if (!this.authUser || !user) return false;
        if (!can('user.update')) return false;
        if (!role(RoleEnum.ADMIN)) return false;

        // Admin puede editar a cualquiera incluyéndose a sí mismo
        return true;
    };

    // Solo admin puede eliminar usuarios, pero no a sí mismo
    // Evita que Admin elimine a otro Admin salvo a sí mismo (aunque no puede)
    public canDeleteUser = (user: User): boolean => {
        if (!this.authUser || !user) return false;
        if (!can('user.delete')) return false;
        if (!role(RoleEnum.ADMIN)) return false;

        // No puede eliminarse a sí mismo
        if (this.authUser.id === user.ulid) return false;

        // Admin no puede eliminar a otro Admin
        if (this.isAdmin(user)) return false;

        return true;
    };
}

// Export a singleton instance for use across the application
export const userPolicy = new UserPolicy();
