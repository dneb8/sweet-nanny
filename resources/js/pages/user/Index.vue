<script lang="ts" setup>
import { Head } from '@inertiajs/vue3'
import type { FetcherResponse } from '@/types/FetcherResponse'
import type { User } from '@/types/User'
import UserTable from './components/UserTable.vue'

const props = defineProps<{
  users: FetcherResponse<User>
}>()

console.log('Users:', props.users.data)

// Asegúrate de enviar el array de usuarios (por ejemplo: users.data)
</script>

<template>
  <Head title="Usuarios" />
  <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
    <!-- Aquí pasas users.data que es el array de usuarios -->
    <UserTable :resource="users.data" />
  </div>
</template>
