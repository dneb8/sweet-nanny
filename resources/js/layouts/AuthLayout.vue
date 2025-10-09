<script setup lang="ts">
import BaseHeaderLoginRegister from '@/Pages/auth/BaseHeaderLogin-Register.vue';
</script>

<template>

    
  <div class="flex flex-col min-h-screen">
    <!-- HEADER -->
    <BaseHeaderLoginRegister>
      <template #logo>
        <img src="/images/Logo-SweetNanny-Claro.svg" alt="Logo" class="h-10" />
      </template>
    </BaseHeaderLoginRegister>

    <!-- CONTENIDO -->
    <main class="flex-1">
      <slot />
    </main>
  </div>
</template>
