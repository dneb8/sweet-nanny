<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import type { FetcherResponse } from '@/types/FetcherResponse'
import type { User } from '@/types/User'

import CardList from '@/components/datatable/CardList.vue'
import UserCard from './components/UserCard.vue'

defineProps<{
    users: FetcherResponse<User>
    searchables: string[]
    sortables: string[]
}>()

</script>

<template>
  <Head title="Usuarios" />
    <CardList
      :items="users.data"
      :per-page="9"
      :sortables="sortables"
      :searchables="searchables"
    >
      <template #default="{ item }">
        <UserCard :user="item" :columns="['name', 'email']"/>
      </template>
    </CardList>

</template>
