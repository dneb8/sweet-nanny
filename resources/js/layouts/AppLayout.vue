<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { useToast } from '@/composables/useToast';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const routeKey = computed(() => page.url);
const { success, error, warning, info } = useToast();

// Watch for flash messages and display toasts
const displayedMessages = new Set<string>();

watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;

        // Handle 'message' object with title and description
        if (flash.message && typeof flash.message === 'object' && 'title' in flash.message) {
            const messageKey = `message:${flash.message.title}:${flash.message.description || ''}`;
            if (!displayedMessages.has(messageKey)) {
                success(flash.message.title, flash.message.description);
                displayedMessages.add(messageKey);
                // Clean up after some time to allow same message later
                setTimeout(() => displayedMessages.delete(messageKey), 10000);
            }
        }

        // Handle success messages
        if (flash.success) {
            const messageKey = `success:${flash.success}`;
            if (!displayedMessages.has(messageKey)) {
                success(flash.success);
                displayedMessages.add(messageKey);
                setTimeout(() => displayedMessages.delete(messageKey), 10000);
            }
        }

        // Handle status messages (treat as success)
        if (flash.status) {
            const messageKey = `status:${flash.status}`;
            if (!displayedMessages.has(messageKey)) {
                success(flash.status);
                displayedMessages.add(messageKey);
                setTimeout(() => displayedMessages.delete(messageKey), 10000);
            }
        }

        // Handle error messages
        if (flash.error) {
            const messageKey = `error:${flash.error}`;
            if (!displayedMessages.has(messageKey)) {
                error(flash.error);
                displayedMessages.add(messageKey);
                setTimeout(() => displayedMessages.delete(messageKey), 10000);
            }
        }

        // Handle warning messages
        if (flash.warning) {
            const messageKey = `warning:${flash.warning}`;
            if (!displayedMessages.has(messageKey)) {
                warning(flash.warning);
                displayedMessages.add(messageKey);
                setTimeout(() => displayedMessages.delete(messageKey), 10000);
            }
        }

        // Handle info messages
        if (flash.info) {
            const messageKey = `info:${flash.info}`;
            if (!displayedMessages.has(messageKey)) {
                info(flash.info);
                displayedMessages.add(messageKey);
                setTimeout(() => displayedMessages.delete(messageKey), 10000);
            }
        }
    },
    { immediate: true, deep: true }
);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <UApp>
            <div :key="routeKey" class="p-5 sm:p-10 max-w-7xl mx-auto w-full h-full" v-gsap.entrance.slide-left>
                <slot />
            </div>
        </UApp>
    </AppLayout>
</template>
