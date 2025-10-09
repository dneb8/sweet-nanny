<template>
  <header class="w-full bg-[#f4c0b9] shadow-md">
    <div class="max-w-7xl mx-auto flex items-center h-16 px-4">
      <!-- Slot para logo -->
      <slot name="logo"></slot>
    </div>
  </header>
</template>

<script setup lang="ts">
// Componente solo de layout
</script>

<style scoped>
/* Asegura que el header ocupe toda la barra superior */
header {
  position: relative;
  top: 0;
  left: 0;
  z-index: 50;
}
</style>
