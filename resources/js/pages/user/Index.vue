<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import type { FetcherResponse } from '@/types/FetcherResponse'
import type { User } from '@/types/User'
import Heading from '@/components/Heading.vue';
import UserFilter from './components/UserFilters.vue'
import CardList from '@/components/datatable/CardList.vue'
import UserCard from './components/UserCard.vue'
import { Button } from '@/components/ui/button'

defineProps<{
    users: FetcherResponse<User>
    roles: Array<string>
    searchables: string[]
    sortables: string[]
}>()

</script>                                                                                                                                                                                                                                               

<template>
  <Head title="Usuarios" />
    <div class="flex flex-row justify-between mb-4">
      <Heading icon='proicons:person-multiple' title="Listado de Usuarios"/>
      <Link :href="route('users.create')">
        <Button> 
          <Icon icon="ri:user-add-line" width="48" height="48" />
          Crear Usuario
        </Button>
      </Link>
    </div>
      <CardList
      :items="users.data"
      :per-page="9"
      :sortables="sortables"
      :searchables="searchables"
      :FilterPanel="UserFilter"
    >
      <template #default="{ item }">
        <UserCard :user="item" :columns="['name', 'email']"/>
      </template>
    </CardList>
</template>
