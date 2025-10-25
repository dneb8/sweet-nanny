import { computed, provide, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Review } from '@/types/Review';

export interface FiltrosReview {
    approved: string | null;
}

export class ReviewTableService {
    // Propiedades reactivas del componente
    public filtros = ref<FiltrosReview>({
        approved: null,
    });

    // Constante reactiva que contiene las columnas visibles
    public visibleColumns = ref<Array<string>>(['Calificación', 'Comentario', 'Para', 'Estado', 'Fecha', 'Acciones']);

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
                    // La notificación se muestra automáticamente por el sistema de toasts
                },
            },
        );
    };

    // Helper para obtener el nombre del reviewable
    public getReviewableName = (review: Review): string => {
        if (!review.reviewable) return '—';

        // Check if it has a user property (for Nanny/Tutor)
        if ('user' in review.reviewable && review.reviewable.user) {
            return review.reviewable.user.name || '—';
        }

        return '—';
    };

    // Helper para obtener el tipo de reviewable
    public getReviewableType = (review: Review): string => {
        if (!review.reviewable_type) return '—';

        const type = review.reviewable_type.split('\\').pop();
        return type === 'Nanny' ? 'Niñera' : type === 'Tutor' ? 'Tutor' : type || '—';
    };
}
