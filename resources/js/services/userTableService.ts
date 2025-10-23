import { computed, provide, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { User } from "@/types/User";
import { RoleEnum } from "@/enums/role.enum";
import { FiltrosUser } from "@/Pages/User/components/UserFiltros.vue";

export class UserTableService {
    // Propiedades reactivas del componente
    public usuarioEliminar = ref<User|null>(null);

    public modalEliminarUsuario = ref(false);

    // Constante reactiva que contiene los filtros de la tabla
    public filtros = ref<FiltrosUser>({
        role: null,
    });

    // Constante reactiva que contiene las columnas visibles
    public visibleColumns = ref<Array<string>>([
        'Perfil',
        'Nombre',
        'Apellidos',
        'Correo Electrónico',
        'Acciones', 
        'Rol',
    ]);

    public constructor() {
        // Se llaman a los providers para pasarle datos a los filtros de user
        provide("users_filters", computed(() => this.getFilters()));
        provide("clear_users_filters", () => {
            this.filtros.value = {
                role: null,
            };
        });
    }

    // Getter de filtros
    public getFilters = () => ({
        role: this.filtros.value.role,
    });

    // Función para redirigir al perfil de una persona.
    public verUsuarioPerfil = (user: User) => {
        router.get(route('users.show', user.ulid));
    }

    // Función que redirige a la vista para editar usuario
    public editarUsuario = (user: User) => {
        router.get(route('users.edit', user.ulid));
    };

    // Función que muestra el modal para eliminar usuario
    public abrirModalEliminarUsuario = (user: User) => {
        this.usuarioEliminar.value = user;
        this.modalEliminarUsuario.value = true;
    };

    // Función que cierra el modal para eliminar usuario
    public cerrarModalEliminarUsuario = () => {
        this.modalEliminarUsuario.value = false;
    };

    // Función para eliminar usuario
    public eliminarUsuario = () => {
        router.delete(route('users.destroy', this.usuarioEliminar.value?.ulid));

        this.modalEliminarUsuario.value = false;
    };

    public getRoleBadgeClasses = (role: RoleEnum): string => {
        const classes: Record<RoleEnum, string> = {
            [RoleEnum.ADMIN]: 'bg-emerald-200/70 text-emerald-500 dark:bg-emerald-400/25 dark:border dark:border-emerald-400 dark:text-emerald-200',
            [RoleEnum.NANNY]: 'bg-pink-200/70 text-pink-500 dark:bg-pink-400/25 dark:border dark:border-pink-400 dark:text-pink-200',
            [RoleEnum.TUTOR]: 'bg-sky-200/70 text-sky-500 dark:bg-sky-400/25 dark:border dark:border-sky-400 dark:text-sky-200',
        };
        return classes[role] ?? '';
    }
}
