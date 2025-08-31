<script lang="ts" setup>
defineProps({
  show: Boolean,
  sortables: Array,
})

const selectedFilters = defineModel('selectedFilters', { type: Object })

import Input from '@/components/ui/input/Input.vue'
</script>

<template>
  <div
    v-if="show"
    class="p-4 space-y-1]"
  >
    <h4 class="text-sm font-semibold mb-2 text-gray-700 dark:text-gray-200">Filtrar por:</h4>
    <div class="space-y-3">
      <div v-for="field in sortables" :key="field" class="flex flex-col">
        <label class="text-xs font-medium text-gray-600 dark:text-gray-300 capitalize">{{ field }}</label>
        <Input
          v-model="selectedFilters[field]"
          class="text-sm mt-2"
          placeholder="Valor exacto"
        />
      </div>
    </div>
  </div>
</template>


