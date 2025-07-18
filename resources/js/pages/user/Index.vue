<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import type { FetcherResponse } from '@/types/FetcherResponse'
import type { User } from '@/types/User'

import CardList from '@/components/datatable/CardList.vue'
import UserCard from './components/UserCard.vue'

defineProps<{
  users: FetcherResponse<User>
}>()

</script>

<template>
  <Head title="Usuarios" />
  <div class="p-4">
    <CardList
      :items="users.data"
      filter-column="name"
      :per-page="9"
      :title="'Listado de Usuarios'"
    >
      <template #default="{ item }">
        <UserCard :user="item" :columns="['name', 'email']"/>
      </template>
    </CardList>
  </div>
</template>
