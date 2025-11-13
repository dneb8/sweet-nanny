<script setup lang="ts">
import { watch, computed } from 'vue';
import { FormControl, FormField, FormItem, FormMessage, FormLabel } from '@/components/ui/form';
// import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { Icon } from '@iconify/vue';
import { ReviewFormService } from '@/services/ReviewFormService';

const props = defineProps<{
    reviewableType: 'tutor' | 'nanny';
    reviewableId: number | string;
    onSuccess?: () => void;
}>();

const emit = defineEmits<{
    (e: 'saved', payload?: any): void;
    (e: 'cancel'): void;
}>();

// Service setup with polymorphic data
const formService = new ReviewFormService({
    reviewableType: props.reviewableType,
    reviewableId: props.reviewableId,
});

const { errors, loading, saved, values, setFieldValue } = formService;

// Star rating state
const hoveredRating = computed(() => 0);

const setRating = (rating: number) => {
    setFieldValue('rating', rating);
};

const canSubmit = computed(() => {
    return (values.rating ?? 0) > 0 && (values.comments ?? '').trim().length > 0 && !loading.value;
});

watch(
    () => saved.value,
    (ok) => {
        if (!ok) return;
        emit('saved', formService.savedReview.value);
        if (props.onSuccess) {
            props.onSuccess();
        }
    }
);

const submit = async () => {
    if (!canSubmit.value) return;
    await formService.saveReview();
};

const cancel = () => {
    emit('cancel');
};
</script>

<template>
    <div class="space-y-6">
        <!-- Rating Stars -->
        <FormField name="rating">
            <FormItem>
                <FormLabel class="text-base font-medium">Calificación *</FormLabel>
                <FormControl>
                    <div class="flex items-center gap-2">
                        <button
                            v-for="star in 5"
                            :key="star"
                            type="button"
                            @click="setRating(star)"
                            class="transition-transform hover:scale-110"
                        >
                            <Icon
                                :icon="star <= (values.rating || hoveredRating) ? 'lucide:star' : 'lucide:star'"
                                :class="[
                                    'w-8 h-8 transition-colors',
                                    star <= (values.rating || hoveredRating)
                                        ? 'text-yellow-500 fill-yellow-500'
                                        : 'text-gray-300 dark:text-gray-600',
                                ]"
                            />
                        </button>
                        <span v-if="values.rating > 0" class="ml-2 text-sm text-muted-foreground">
                            {{ values.rating }} {{ values.rating === 1 ? 'estrella' : 'estrellas' }}
                        </span>
                    </div>
                </FormControl>
                <FormMessage>{{ errors['rating']?.[0] }}</FormMessage>
            </FormItem>
        </FormField>

        <!-- Comments -->
        <FormField v-slot="{ componentField }" name="comments">
            <FormItem>
                <FormLabel class="text-base font-medium">Comentarios *</FormLabel>
                <FormControl>
                    <Textarea
                        v-bind="componentField"
                        placeholder="Comparte tu experiencia..."
                        class="min-h-[120px] resize-none"
                        maxlength="1000"
                    />
                </FormControl>
                <div class="flex justify-between items-center">
                    <FormMessage>{{ errors['comments']?.[0] }}</FormMessage>
                    <span class="text-xs text-muted-foreground">{{ values.comments.length }}/1000</span>
                </div>
            </FormItem>
        </FormField>

        <!-- Info Note -->
        <div class="rounded-lg border border-blue-200 bg-blue-50 dark:bg-blue-950/30 dark:border-blue-800 p-3">
            <div class="flex gap-2">
                <Icon icon="lucide:info" class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                <p class="text-sm text-blue-900 dark:text-blue-100">
                    Tu reseña será revisada por un administrador antes de ser publicada.
                </p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-3 pt-2">
            <Button type="button" variant="outline" @click="cancel" :disabled="loading">Cancelar</Button>
            <Button type="button" @click="submit" :disabled="!canSubmit">
                <Icon v-if="loading" icon="lucide:loader-2" class="w-4 h-4 mr-2 animate-spin" />
                <span v-if="loading">Enviando...</span>
                <span v-else>Enviar Reseña</span>
            </Button>
        </div>
    </div>
</template>
