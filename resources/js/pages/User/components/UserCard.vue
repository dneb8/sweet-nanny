<!-- components/UserCard.vue -->
<script setup lang="ts">
import type { User } from '@/types/User'

defineProps<{
  user: User
  columns: string[] // Ej. ['name', 'email', 'roles']
}>()
</script>

<template>
  <div class="rounded-xl border shadow p-4 bg-white space-y-2">
    <div v-if="columns.includes('name')">
      <h2 class="text-lg font-bold">{{ user.name }}</h2>
    </div>
    <div v-if="columns.includes('email')">
      <p class="text-sm text-gray-600">{{ user.email }}</p>
    </div>
  </div>
</template>
