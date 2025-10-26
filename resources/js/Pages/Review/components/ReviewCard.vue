<script setup lang="ts">
import { Card, CardHeader, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Icon } from '@iconify/vue';
import type { Review } from '@/types/Review';
import { ReviewTableService } from '@/services/reviewTableService';
import Badge from '@/components/common/Badge.vue';
import { Star } from 'lucide-vue-next';

const props = defineProps<{
    review: Review;
}>();

const { toggleApproved, getReviewableName, getReviewableType } = new ReviewTableService();

// Helper para generar estrellas
const getStars = (rating: number): string => {
    return '⭐'.repeat(Math.min(Math.max(rating, 0), 5));
};
</script>

<template>
    <Card class="relative overflow-hidden">
        <!-- Cabecera -->
        <CardHeader class="flex flex-row gap-4 items-start px-4">
            <!-- Icon -->
            <div class="flex-none w-20 flex flex-col items-center">
                <div class="w-16 h-16 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center border overflow-hidden">
                    <Star class="w-8 h-8 text-yellow-500 fill-yellow-500" />
                </div>
            </div>

            <!-- Info review -->
            <div class="flex-1 min-w-0">
                <!-- Calificación -->
                <div class="flex items-center gap-2">
                    <span class="text-lg">{{ getStars(props.review.rating) }}</span>
                    <span class="text-sm text-muted-foreground">{{ props.review.rating }}/5</span>
                </div>

                <!-- Estado -->
                <div class="mt-2">
                    <Badge
                        v-if="props.review.approved"
                        label="Aprobado"
                        customClass="bg-emerald-200/70 text-emerald-500 dark:bg-emerald-400/25 dark:border dark:border-emerald-400 dark:text-emerald-200"
                    />
                    <Badge
                        v-else
                        label="Pendiente"
                        customClass="bg-amber-200/70 text-amber-500 dark:bg-amber-400/25 dark:border dark:border-amber-400 dark:text-amber-200"
                    />
                </div>

                <!-- Para quién -->
                <div class="mt-2">
                    <Badge label="Para:" customClass="bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300" />
                    <span class="ml-2 text-sm font-medium">{{ getReviewableName(props.review) }}</span>
                    <span class="ml-1 text-xs text-muted-foreground">({{ getReviewableType(props.review) }})</span>
                </div>
            </div>
        </CardHeader>

        <!-- Contenido: comentario -->
        <CardContent class="px-4 pb-4">
            <p class="text-sm text-muted-foreground line-clamp-3">{{ props.review.comments || 'Sin comentarios' }}</p>

            <!-- Fecha -->
            <div class="text-xs text-muted-foreground mt-3">
                {{ new Date(props.review.created_at).toLocaleDateString('es-ES') }}
            </div>

            <!-- Acción de toggle -->
            <div class="mt-3 flex justify-end">
                <Button
                    variant="outline"
                    size="sm"
                    @click="toggleApproved(props.review)"
                    :class="[
                        props.review.approved
                            ? 'text-emerald-600 hover:text-emerald-700 border-emerald-200'
                            : 'text-amber-600 hover:text-amber-700 border-amber-200',
                    ]"
                >
                    <Icon :icon="props.review.approved ? 'mdi:earth' : 'mdi:earth-off'" class="w-4 h-4 mr-2" />
                    {{ props.review.approved ? 'Público' : 'Privado' }}
                </Button>
            </div>
        </CardContent>
    </Card>
</template>
