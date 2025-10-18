<script setup lang="ts">
import BaseHeaderLoginRegister from '@/Pages/auth/BaseHeaderLogin-Register.vue';
import { defineProps, withDefaults } from 'vue';
import { useFlashMessages } from '@/composables/useFlashMessages';

const props = withDefaults(defineProps<{ header?: boolean }>(), {
    header: true,
});

// Enable automatic toast notifications from flash messages
useFlashMessages();
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
