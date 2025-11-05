import { Nanny } from './Nanny';
import { Tutor } from './Tutor';

export interface Review {
    id: number;
    reviewable_type: string;
    reviewable_id: number;
    rating: number;
    comments?: string;
    approved: boolean;
    created_at: string;
    updated_at: string;

    // Relaciones
    reviewable?: Nanny | Tutor;
    nanny?: Nanny;
    tutor?: Tutor;
}
