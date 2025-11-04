<script setup lang="ts">
import DataTable from '@/components/datatable/Main.vue';
import Column from '@/components/datatable/Column.vue';
import type { FetcherResponse } from '@/types/FetcherResponse';
import type { Review } from '@/types/Review';
import { ReviewTableService } from '@/services/reviewTableService';
import ReviewFiltros from './ReviewFiltros.vue';
import ReviewCard from './ReviewCard.vue';
import Badge from '@/components/common/Badge.vue';
import { getRoleLabelByString } from "@/enums/role.enum";
import { getUserInitials } from "@/utils/getUserInitials";
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';

defineProps<{
    resource: FetcherResponse<Review>;
}>();

// Servicio que expone estado + handlers
const { filtros, visibleColumns, toggleApproved, getReviewableName, verUsuarioPerfil, getRoleBadgeClasses } = new ReviewTableService();

// Helper para generar estrellas
const getStars = (rating: number): string => {
    return '⭐'.repeat(Math.min(Math.max(rating, 0), 5));
};
</script>

<template>
    <DataTable
        :resource="resource"
        resourcePropName="reviews"
        use-filters
        :canToggleColumnsVisibility="true"
        v-model:visibleColumns="visibleColumns"
        :responsiveCards="'lg'"
    >
        <!-- Filtros -->
        <template #filters>
            <ReviewFiltros v-model:filtros="filtros" />
        </template>

        <!-- Card responsivo -->
        <template #responsive-card="{ slotProps }">
            <ReviewCard :review="slotProps" />
        </template>

        <!-- Calificación -->
        <Column header="Calificación" field="rating" :sortable="true">
            <template #body="slotProps">
                <div class="flex items-center gap-2">
                    <span class="text-lg">{{ getStars(slotProps.record.rating) }}</span>
                    <span class="text-sm text-muted-foreground">{{ slotProps.record.rating }}/5</span>
                </div>
            </template>
        </Column>

        <!-- Comentario -->
        <Column header="Comentario">
            <template #body="{ record }">
                <div class="max-w-sm">
                <template v-if="record?.comments">
                    <div
                    class="rounded-md border bg-background/50 overflow-x-auto"
                    :title="record.comments"
                    >
                    <div class="p-2 pr-3 text-sm leading-snug whitespace-nowrap inline-block min-w-max">
                        {{ record.comments }}
                    </div>
                    </div>
                </template>

                <template v-else>
                    <span class="text-muted-foreground">Sin comentarios</span>
                </template>
                </div>
            </template>
        </Column>

        <!-- Columna: Perfil (avatar + click a perfil de usuario) -->
        <Column header="Perfil" field="id">
        <template #body="slotProps">
            <div
            @click="verUsuarioPerfil(slotProps.record?.reviewable?.user)"
            class="flex items-center gap-2 cursor-pointer hover:text-rose-400 dark:hover:text-rose-300"
            title="Ver perfil"
            >
            <Avatar shape="square" size="sm" class="cursor-pointer overflow-hidden">
                <!-- Si tienes avatar_url en reviewable.user -->
                <AvatarImage
                v-if="slotProps.record?.reviewable?.user?.avatar_url"
                :src="slotProps.record.reviewable.user.avatar_url"
                :alt="slotProps.record?.reviewable?.user?.name ?? 'avatar'"
                class="h-8 w-8 object-cover"
                />
                <AvatarFallback v-else>
                {{ getUserInitials(slotProps.record?.reviewable?.user) }}
                </AvatarFallback>
            </Avatar>
            </div>
        </template>
        </Column>

        <!-- Columna: Nombre -->
        <Column header="Nombre">
        <template #body="slotProps">
            <span class="text-sm">
            {{ getReviewableName(slotProps.record) ?? 'Sin nombre' }}
            </span>
        </template>
        </Column>

        <!-- Columna: Rol (badge) -->
        <Column header="Rol">
        <template #body="slotProps">
            <div class="flex items-center gap-2">
            <Badge
                :label="getRoleLabelByString(slotProps.record?.reviewable?.user?.roles?.[0]?.name ?? '') || 'Sin rol'"
                :customClass="getRoleBadgeClasses(slotProps.record?.reviewable?.user?.roles?.[0]?.name ?? '')"
            />
            </div>
        </template>
        </Column>

        <!-- Estado (Approved) -->
        <Column header="Estado" field="approved" :sortable="true">
            <template #body="slotProps">
                <Badge
                    v-if="slotProps.record.approved"
                    label="Aprobado"
                    customClass="bg-emerald-200/70 text-emerald-500 dark:bg-emerald-400/25 dark:border dark:border-emerald-400 dark:text-emerald-200"
                />
                <Badge
                    v-else
                    label="Pendiente"
                    customClass="bg-amber-200/70 text-amber-500 dark:bg-amber-400/25 dark:border dark:border-amber-400 dark:text-amber-200"
                />
            </template>
        </Column>

        <!-- Fecha -->
        <Column header="Fecha" field="created_at" :sortable="true">
            <template #body="slotProps">
                {{ new Date(slotProps.record.created_at).toLocaleDateString('es-ES') }}
            </template>
        </Column>

        <!-- Acciones con iconos más grandes -->
        <Column header="Acciones" field="id">
        <template #body="p">
            <button
            @click="toggleApproved(p.record)"
            :title="p.record.approved ? 'Desaprobar' : 'Aprobar'"
            :class="[
                'flex h-8 w-8 items-center justify-center rounded-md',
                p.record.approved ? 'text-emerald-600' : 'text-amber-600'
            ]"
            >
            <Icon
                :icon="p.record.approved ? 'mdi:earth' : 'mdi:earth-off'"
                class="block leading-none"
                :width="18" :height="18"
            />
            </button>
        </template>
        </Column>

    </DataTable>
</template>
