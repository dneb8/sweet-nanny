<script setup lang="ts">
import { Icon } from '@iconify/vue';
import type { Review } from '@/types/Review';
import { ReviewTableService } from '@/services/reviewTableService';
import Badge from '@/components/common/Badge.vue';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { getUserInitials } from '@/utils/getUserInitials';
import { getRoleLabelByString } from '@/enums/role.enum';

const props = defineProps<{
    review: Review;
}>();

const { toggleApproved, getReviewableName, getRoleBadgeClasses } = new ReviewTableService();

// Helper para generar estrellas
const getStars = (rating: number): string => {
    return '⭐'.repeat(Math.min(Math.max(rating, 0), 5));
};
</script>

<template>
    <!-- Card with styling matching desktop table rows -->
    <div class="bg-white/50 dark:bg-background/50 border border-foreground/20 rounded-lg p-3 space-y-3">
        <!-- Calificación y Avatar -->
        <div class="flex items-center gap-3">
            <Avatar shape="square" size="sm" class="overflow-hidden">
                <AvatarImage
                    v-if="props.review?.reviewable?.user?.avatar_url"
                    :src="props.review.reviewable.user.avatar_url"
                    :alt="props.review?.reviewable?.user?.name ?? 'avatar'"
                    class="h-8 w-8 object-cover"
                />
                <AvatarFallback v-else>
                    {{ getUserInitials(props.review?.reviewable?.user) }}
                </AvatarFallback>
            </Avatar>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-lg">{{ getStars(props.review.rating) }}</span>
                    <span class="text-sm text-muted-foreground">{{ props.review.rating }}/5</span>
                </div>
                <div class="text-sm text-foreground/80 truncate">
                    {{ getReviewableName(props.review) ?? 'Sin nombre' }}
                </div>
            </div>
        </div>

        <!-- Rol -->
        <div class="flex items-center gap-2">
            <span class="text-xs text-muted-foreground font-medium">Rol:</span>
            <Badge
                :label="getRoleLabelByString(props.review?.reviewable?.user?.roles?.[0]?.name ?? '') || 'Sin rol'"
                :customClass="getRoleBadgeClasses(props.review?.reviewable?.user?.roles?.[0]?.name ?? '')"
            />
        </div>

        <!-- Comentario -->
        <div>
            <div class="text-xs text-muted-foreground font-medium mb-1">Comentario</div>
            <div class="text-sm text-foreground/80 line-clamp-3">
                {{ props.review.comments || 'Sin comentarios' }}
            </div>
        </div>

        <!-- Estado -->
        <div class="flex items-center gap-2">
            <span class="text-xs text-muted-foreground font-medium">Estado:</span>
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

        <!-- Fecha -->
        <div class="text-xs text-muted-foreground">{{ new Date(props.review.created_at).toLocaleDateString('es-ES') }}</div>

        <!-- Acción de toggle -->
        <div class="flex justify-end pt-2 border-t border-foreground/20">
            <button
                @click="toggleApproved(props.review)"
                :title="props.review.approved ? 'Desaprobar' : 'Aprobar'"
                :class="[
                    'flex h-8 w-8 items-center justify-center rounded-md',
                    props.review.approved ? 'text-emerald-600 dark:text-emerald-500' : 'text-amber-600 dark:text-amber-500',
                ]"
            >
                <Icon :icon="props.review.approved ? 'mdi:earth' : 'mdi:earth-off'" class="block leading-none" :width="18" :height="18" />
            </button>
        </div>
    </div>
</template>
