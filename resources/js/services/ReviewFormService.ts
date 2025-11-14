import { ref, type Ref } from 'vue';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import { useForm as useInertiaForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import * as z from 'zod';
import type { Review } from '@/types/Review';

type Ctor = {
    reviewableType: 'tutor' | 'nanny';
    reviewableId: number | string;
};

export class ReviewFormService {
    public formSchema;
    public values;
    public isFieldDirty;
    public setFieldValue: (field: string, value: any) => void;

    public loading = ref<boolean>(false);
    public saved = ref<boolean>(false);
    public errors = ref<Record<string, string[]>>({});
    public savedReview = ref<Review | null>(null);

    public saveReview: (e?: Event) => void;

    constructor({ reviewableType, reviewableId }: Ctor) {
        // Convert reviewableType to FQCN for backend
        const reviewableTypeFQCN = reviewableType === 'tutor' ? 'App\\Models\\Tutor' : 'App\\Models\\Nanny';

        // Validation schema
        this.formSchema = toTypedSchema(
            z.object({
                reviewable_type: z.string().nonempty('El tipo es obligatorio'),
                reviewable_id: z.number().int().positive().or(z.string().nonempty()),
                rating: z.number().int().min(1, 'Debes seleccionar al menos 1 estrella').max(5, 'Máximo 5 estrellas'),
                comments: z
                    .string()
                    .nonempty('Los comentarios son obligatorios')
                    .max(1000, 'Los comentarios no pueden exceder 1000 caracteres'),
                approved: z.boolean().default(false),
            })
        );

        const { values, isFieldDirty, handleSubmit, setFieldValue } = useForm({
            validationSchema: this.formSchema,
            initialValues: {
                reviewable_type: reviewableTypeFQCN,
                reviewable_id: typeof reviewableId === 'string' ? parseInt(reviewableId) : reviewableId,
                rating: 0,
                comments: '',
                approved: false, // Always false for user submissions
            },
        });

        this.values = values;
        this.isFieldDirty = isFieldDirty;
        this.setFieldValue = setFieldValue as unknown as (field: string, value: any) => void;

        // Create review
        this.saveReview = handleSubmit(async (vals) => {
            this.loading.value = true;
            this.errors.value = {};

            const form = useInertiaForm(vals);

            form.post(route('reviews.store'), {
                preserveState: true,
                onSuccess: () => {
                    this.saved.value = true;
                    const p: any = usePage().props;
                    this.savedReview.value = p?.recent?.review ?? null;
                },
                onError: (errs: Record<string, any>) => {
                    const normalized: Record<string, string[]> = {};
                    Object.entries(errs).forEach(([k, v]) => (normalized[k] = Array.isArray(v) ? v : [v]));
                    this.errors.value = normalized;
                },
                onFinish: () => (this.loading.value = false),
            });
        });
    }
}
