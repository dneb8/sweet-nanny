<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import type { FetcherResponse } from '@/types/FetcherResponse'
import type { Nanny } from '@/types/Nanny'
import Heading from '@/components/Heading.vue';
import NannyFilter from './components/NannyFilters.vue'
import CardList from '@/components/datatable/CardList.vue'
import NannyCard from './components/NannyCard.vue'
import { Button } from '@/components/ui/button'

defineProps<{
    nannies: FetcherResponse<Nanny>
    searchables: string[]
    sortables: string[]
}>()

</script>                                                                                                                                                                                                                                               

<template>
  <Head title="Niñeras" />
    <div class="flex flex-row justify-between mb-4">
      <Heading icon='proicons:person-multiple' title="Listado de Niñeras"/>
      <Link :href="route('nannies.create')">
        <Button> 
          <Icon icon="nanny-add-line" width="48" height="48" />
          Crear Usuario
        </Button>
      </Link>
    </div>
      <!-- <CardList
      :items="nannies.data"
      :per-page="9"
      :sortables="sortables"
      :searchables="searchables"
      :FilterPanel="NannyFilter"
    >
      <template #default="{ item }">
        <NannyCard :nanny="item" :columns="['name', 'email']"/>
      </template>
    </CardList> -->
</template>
