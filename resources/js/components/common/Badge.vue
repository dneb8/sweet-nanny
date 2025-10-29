<script setup lang="ts">
import { defineProps } from 'vue'

defineProps<{
  label: string
  icon?: string
  customClass?: string
}>()
</script>

<template>
  <div
    class="text-xs font-semibold rounded-full p-1 px-2 cursor-default dark:border flex gap-1 items-center justify-center w-fit"
    :class="customClass"
  >
    <Icon v-if="icon" :icon="icon" class="min-w-[1.2em] min-h-[1.2em] text-base" />
    <span>{{ label }}</span>
  </div>
</template>

