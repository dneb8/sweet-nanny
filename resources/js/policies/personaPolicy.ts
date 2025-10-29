import { RoleEnum } from "@/enums/role.enum";
import { usePage } from "@inertiajs/vue3";
import { Rol } from "@/types/Rol";
import { can, role } from "@/helpers/permissionHelper";
import { PageProps } from "@/types/UsePage";
import { Persona } from "@/types/Persona";

export class PersonaPolicy {
    // Persona del usuario autenticado
    private authPersona = usePage<PageProps>().props.auth.persona;

    // Método para determinar si una persona es desarrollador
    private esDesarrollador = (persona: Persona) => {
        return persona.roles.filter((rol: Rol) => rol.name === RoleEnum.DESARROLLADOR).length > 0;
    };

    // Método para determinar si una persona es operativo
    public esOperativo = (persona: Persona) => {
        return persona.roles.filter((rol: Rol) => rol.name === RoleEnum.OPERATIVO).length > 0;
    };

    // Método para determinar si una persona es administrador
    private esAdministrador = (persona: Persona) => {
        return persona.roles.filter((rol: Rol) => rol.name === RoleEnum.ADMINISTRADOR).length > 0;
    };

    // Método para determinar si se puede ver una persona
    public canViewPersona = (persona: Persona) => {
        if (! can('persona.show')) {
            return false;
        }

        if (! role(RoleEnum.DESARROLLADOR) && this.esDesarrollador(persona)) {
            return false;
        }

        return true;
    }

    // Método para determinar si se puede editar una persona
    public canUpdatePersona = (persona: Persona) => {
        if (! can('persona.update')) {
            return false;
        }

        if (role(RoleEnum.DESARROLLADOR) && this.esDesarrollador(persona) && this.authPersona.id !== persona.id) {
            return false;
        }

        if (role(RoleEnum.ADMINISTRADOR) && this.esDesarrollador(persona)) {
            return false;
        }

        if (role(RoleEnum.ADMINISTRADOR) && this.esAdministrador(persona) && this.authPersona.id !== persona.id) {
            return false;
        }

        return true;
    };

    // Método para determinar si se pueden asignar habilidades a una persona
    public canAsignarHabilidad = (persona: Persona) => {
        if (! can('persona.asignar-habilidad')) {
            return false;
        }

        if (! this.esOperativo(persona)) {
            return false;
        }

        return true;
    };

    // Método para determinar si se puede eliminar una persona
    public canDeletePersona = (persona: Persona) => {
        if (! can('persona.delete')) {
            return false;
        }

        if (this.authPersona.id === persona.id) {
            return false;
        }

        if (role(RoleEnum.DESARROLLADOR) && this.esDesarrollador(persona)) {
            return false;
        }

        if (role(RoleEnum.ADMINISTRADOR) && this.esDesarrollador(persona)) {
            return false;
        }

        if (role(RoleEnum.ADMINISTRADOR) && this.esAdministrador(persona)) {
            return false;
        }

        if (persona.user_id !== null) {
            return false;
        }

        return true;
    };
}
