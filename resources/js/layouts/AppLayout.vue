<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const routeKey = computed(() => page.url); 
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div :key="routeKey" class="p-4 px-8 max-w-7xl mx-auto w-full h-full" v-gsap.entrance.slide-left>
            <slot />
        </div>
    </AppLayout>
</template>
