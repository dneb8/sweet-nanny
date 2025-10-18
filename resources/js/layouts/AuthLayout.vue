<script setup lang="ts">
import BaseHeaderLoginRegister from '@/Pages/auth/BaseHeaderLogin-Register.vue';
import { defineProps, withDefaults, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

const props = withDefaults(defineProps<{ header?: boolean }>(), {
    header: true,
});

const page = usePage();
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
  <div class="flex flex-col min-h-screen">
    <!-- HEADER -->
    <template v-if="props.header !== false">
      <BaseHeaderLoginRegister>
        <template #logo>
          <img src="/images/Logo-SweetNanny-Claro.svg" alt="Logo" class="h-10" />
        </template>
      </BaseHeaderLoginRegister>
    </template>

    <!-- CONTENIDO -->
    <main class="flex-1">
      <slot />
    </main>
  </div>
</template>
