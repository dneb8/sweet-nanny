import { User } from "@/types/User";
import { RoleEnum } from "@/enums/role.enum";
import { usePage } from "@inertiajs/vue3";
import { Rol } from "@/types/Rol";
import { can, role } from "@/helpers/permissionHelper";
import { PageProps } from "@/types/UsePage";

export class UserPolicy {
    // Usuario autenticado
    private authUser = usePage<PageProps>().props.auth.user;

    // Método para determinar si un usuario es desarrollador
    private esDesarrollador = (user: User) => {
        return user.persona.roles.filter((rol: Rol) => rol.name === RoleEnum.DESARROLLADOR).length > 0;
    };

    // Método para determinar si un usuario es administrador
    private esAdministrador = (user: User) => {
        return user.persona.roles.filter((rol: Rol) => rol.name === RoleEnum.ADMINISTRADOR).length > 0;
    };

    // Método para determinar si se puede editar un usuario
    public canUpdateUser = (user: User) => {
        if (! can('user.update')) {
            return false;
        }

        if (role(RoleEnum.DESARROLLADOR) && this.esDesarrollador(user) && this.authUser.id !== user.id) {
            return false;
        }

        if (role(RoleEnum.ADMINISTRADOR) && this.esDesarrollador(user)) {
            return false;
        }

        if (role(RoleEnum.ADMINISTRADOR) && this.esAdministrador(user) && this.authUser.id !== user.id) {
            return false;
        }

        return true;
    };

    // Método para determinar si se puede eliminar un usuario
    public canDeleteUser = (user: User) => {
        if (! can('user.delete')) {
            return false;
        }

        if (this.authUser.id === user.id) {
            return false;
        }

        if (role(RoleEnum.DESARROLLADOR) && this.esDesarrollador(user)) {
            return false;
        }

        if (role(RoleEnum.ADMINISTRADOR) && this.esDesarrollador(user)) {
            return false;
        }

        if (role(RoleEnum.ADMINISTRADOR) && this.esAdministrador(user)) {
            return false;
        }

        return true;
    };
}
