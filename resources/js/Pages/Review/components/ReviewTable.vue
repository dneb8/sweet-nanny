<script setup lang="ts">
import DataTable from '@/components/datatable/Main.vue';
import Column from '@/components/datatable/Column.vue';
import type { FetcherResponse } from '@/types/FetcherResponse';
import type { Review } from '@/types/Review';
import { ReviewTableService } from '@/services/reviewTableService';
import ReviewFiltros from './ReviewFiltros.vue';
import Badge from '@/components/common/Badge.vue';

defineProps<{
    resource: FetcherResponse<Review>;
}>();

// Servicio que expone estado + handlers
const { filtros, visibleColumns, toggleApproved, getReviewableName, getReviewableType } = new ReviewTableService();

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
    >
        <!-- Filtros -->
        <template #filters>
            <ReviewFiltros v-model:filtros="filtros" />
        </template>

        <!-- Columna Calificación -->
        <Column header="Calificación" field="rating" :sortable="true">
            <template #body="slotProps">
                <div class="flex items-center gap-2">
                    <span class="text-lg">{{ getStars(slotProps.record.rating) }}</span>
                    <span class="text-sm text-muted-foreground">{{ slotProps.record.rating }}/5</span>
                </div>
            </template>
        </Column>

        <!-- Columna Comentario -->
        <Column header="Comentario">
            <template #body="slotProps">
                <div class="max-w-md truncate" :title="slotProps.record.comments || ''">
                    {{ slotProps.record.comments || '—' }}
                </div>
            </template>
        </Column>

        <!-- Columna Para (Reviewable) -->
        <Column header="Para">
            <template #body="slotProps">
                <div class="flex flex-col">
                    <span class="font-medium">{{ getReviewableName(slotProps.record) }}</span>
                    <span class="text-xs text-muted-foreground">{{ getReviewableType(slotProps.record) }}</span>
                </div>
            </template>
        </Column>

        <!-- Columna Estado (Approved) -->
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

        <!-- Columna Fecha -->
        <Column header="Fecha" field="created_at" :sortable="true">
            <template #body="slotProps">
                {{ new Date(slotProps.record.created_at).toLocaleDateString('es-ES') }}
            </template>
        </Column>

        <!-- Acciones -->
        <Column header="Acciones" field="id">
            <template #body="slotProps">
                <div class="flex gap-2">
                    <div
                        @click="toggleApproved(slotProps.record)"
                        :class="[
                            'flex justify-center items-center w-max hover:cursor-pointer',
                            slotProps.record.approved
                                ? 'text-emerald-600 dark:text-emerald-500 hover:text-emerald-600/80 dark:hover:text-emerald-400'
                                : 'text-amber-600 dark:text-amber-500 hover:text-amber-600/80 dark:hover:text-amber-400',
                        ]"
                        :title="slotProps.record.approved ? 'Marcar como no aprobado (privado)' : 'Marcar como aprobado (público)'"
                    >
                        <Icon :icon="slotProps.record.approved ? 'mdi:earth' : 'mdi:earth-off'" :size="20" />
                    </div>
                </div>
            </template>
        </Column>
    </DataTable>
</template>
