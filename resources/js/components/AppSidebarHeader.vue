<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import type { BreadcrumbItemType } from '@/types';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { useAppearance } from '@/composables/useAppearance';
import { computed } from 'vue';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const { appearance, updateAppearance } = useAppearance();

const themeIcon = computed(() =>
    appearance.value === 'light' ? 'line-md:moon-alt-to-sunny-outline-loop-transition' : 'line-md:moon-loop'
);

const toggleAppearance = () => {
    updateAppearance(appearance.value === 'light' ? 'dark' : 'light');
};
</script>

<template>
    <header 
        class="sticky top-0 z-50 bg-background/40 backdrop-blur-sm flex h-12 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-1 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12"
    >
        <div class="flex items-center gap-2 p-2">
            <SidebarTrigger/>
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        
        <button @click="toggleAppearance" class="ml-auto p-2 rounded-full hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
            <Icon :icon="themeIcon" width="20" height="20" />
        </button>
    </header>
</template>
