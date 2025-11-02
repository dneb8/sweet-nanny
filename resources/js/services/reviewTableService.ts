import { computed, provide, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Review } from '@/types/Review';
import { RoleEnum } from '@/enums/role.enum';
import type { User } from '@/types/User';

export interface FiltrosReview {
    approved: string | null;
}

export class ReviewTableService {
    // Propiedades reactivas del componente
    public filtros = ref<FiltrosReview>({
        approved: null,
    });

    // Constante reactiva que contiene las columnas visibles
    public visibleColumns = ref<Array<string>>(['Calificación', 'Comentario', 'Perfil', 'Nombre', 'Rol', 'Estado', 'Fecha', 'Acciones']);

    public constructor() {
        // Providers para comunicación con DataTable/Filters
        provide('reviews_filters', computed(() => this.getFilters()));
        provide('clear_reviews_filters', () => {
            this.filtros.value = { approved: null };
        });
    }

    // Getter de filtros
    public getFilters = () => ({
        approved: this.filtros.value.approved,
    });

    // Función para toggle approved status
    public toggleApproved = (review: Review) => {
        router.post(
            route('reviews.toggleApproved', review.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                },
            },
        );
    };

    // Helper para obtener el nombre del reviewable
    public getReviewableName = (review: Review): string => {
        if (!review.reviewable) return '—';

        // Check if it has a user property (for Nanny/Tutor)
        if ('user' in review.reviewable && review.reviewable.user) {
            return (review.reviewable.user.name + ' ' + review.reviewable.user.surnames || '—');
        }

        return '—';
    };

    // Helper para obtener el tipo de reviewable
    public getReviewableType = (review: Review): string => {
        if (!review.reviewable_type) return '—';

        const type = review.reviewable_type.split('\\').pop();
        return type === 'Nanny' ? 'Niñera' : type === 'Tutor' ? 'Tutor' : type || '—';
    };

    // Función para redirigir al perfil de una persona.
    public verUsuarioPerfil = (user: User) => {
        router.get(route('users.show', user.ulid));
    }

    public getRoleBadgeClasses = (role: RoleEnum): string => {
        const classes: Record<RoleEnum, string> = {
            [RoleEnum.ADMIN]: 'bg-emerald-200/70 text-emerald-500 dark:bg-emerald-400/25 dark:border dark:border-emerald-400 dark:text-emerald-200',
            [RoleEnum.NANNY]: 'bg-pink-200/70 text-pink-500 dark:bg-pink-400/25 dark:border dark:border-pink-400 dark:text-pink-200',
            [RoleEnum.TUTOR]: 'bg-sky-200/70 text-sky-500 dark:bg-sky-400/25 dark:border dark:border-sky-400 dark:text-sky-200',
        };
        return classes[role] ?? '';
    }
}
