<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import type { FetcherResponse } from '@/types/FetcherResponse'
import type { Booking } from '@/types/Booking'
import Heading from '@/components/Heading.vue';
import BookingFilters from './partials/BookingFilters.vue'
import CardList from '@/components/datatable/CardList.vue'
import BookingCard from './partials/BookingCard.vue'
import { Button } from '@/components/ui/button'

const props = defineProps<{
    bookings: FetcherResponse<Booking>
    roles: Array<string>
    searchables: string[]
    sortables: string[]
}>()

console.log(props.bookings)

</script>                                                                                                                                                                                                                                               

<template>
  <Head title="Usuarios" />
    <div class="flex flex-row justify-between mb-4">
      <Heading icon='proicons:person-multiple' title="Listado de Usuarios"/>
      <Link :href="route('bookings.create')">
        <Button> 
          <Icon icon="ri:booking-add-line" width="48" height="48" />
          Crear Usuario
        </Button>
      </Link>
    </div>
      <CardList
      :items="bookings.data"
      :per-page="9"
      :sortables="sortables"
      :searchables="searchables"
      :FilterPanel="BookingFilters"
    >
      <template #default="{ item }">
        <BookingCard :booking="item" :columns="['name', 'email']"/>
      </template>
    </CardList>
</template>
