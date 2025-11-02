<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import type { FetcherResponse } from '@/types/FetcherResponse'
import type { Booking } from '@/types/Booking'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'

defineProps<{
  bookings: FetcherResponse<Booking>
  roles: string[]
  searchables: string[]
  sortables: string[]
}>()

const fmtDateTime = (value?: string | Date | null) => {
  if (!value) return '—'
  const d = typeof value === 'string' ? new Date(value) : value
  return isNaN(+d) ? String(value) : new Intl.DateTimeFormat('es-MX', {
    dateStyle: 'medium',
    timeStyle: 'short'
  }).format(d)
}
</script>

<template>
  <Head title="Servicios" />

  <div class="mb-4 flex items-center justify-between">
    <Heading icon="ri:calendar-schedule-line" title="Listado de Servicios" />
    <Link :href="route('bookings.create')">
      <Button class="gap-2">
        <Icon icon="ri:calendar-event-line" width="18" height="18" />
        Crear Servicio
      </Button>
    </Link>
  </div>

  <div v-if="(bookings?.data ?? []).length === 0" class="rounded-lg border p-6 text-center text-sm text-muted-foreground">
    No hay servicios todavía.
  </div>

  <div v-else class="overflow-x-auto rounded-lg border">
    <table class="min-w-full text-sm">
      <thead class="bg-muted/40 text-left">
        <tr>
          <th class="px-4 py-3">ID</th>
          <th class="px-4 py-3">Tutor</th>
          <th class="px-4 py-3">Niños</th>
          <th class="px-4 py-3">Estado</th>
          <th class="px-4 py-3">Citas</th>
          <th class="px-4 py-3">Creado</th>
          <th class="px-4 py-3 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="booking in bookings.data" :key="booking.id" class="border-t">
          <td class="px-4 py-3 font-medium">#{{ booking.id }}</td>
          <td class="px-4 py-3">
            <div class="flex flex-col">
              <span class="font-medium">{{ booking.tutor?.user.name ?? '—' }}</span>
              <span class="text-xs text-muted-foreground">{{ booking.tutor?.user.email ?? '—' }}</span>
            </div>
          </td>
          <td class="px-4 py-3">
            {{ (booking.booking_appointments?.[0]?.children ?? []).map((c: { name: string }) => c.name).join(', ') || '—' }}
          </td>
          <td class="px-4 py-3">
            <span class="inline-flex rounded-full border px-2 py-0.5 text-xs">
              {{ booking.status ?? '—' }}
            </span>
          </td>
          <td class="px-4 py-3">
            {{ booking.booking_appointments?.length ?? 0 }}
          </td>
          <td class="px-4 py-3">
            {{ fmtDateTime(booking.created_at) }}
          </td>
          <td class="px-4 py-3 text-right">
          <Button
            size="sm"
            class="gap-2 bg-background/50 text-foreground hover:bg-background/70"
            @click="router.get(route('bookings.show', booking.id))"
          >
            <Icon icon="ri:eye-line" />
            Ver servicio
          </Button>

          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <nav v-if="(bookings?.links ?? []).length" class="mt-4 flex flex-wrap items-center gap-2" aria-label="Pagination">
    <Link
      v-for="(l, i) in bookings.links"
      :key="i"
      :href="l.url ?? '#'"
      :disabled="l.url === null"
      preserve-scroll
      class="rounded-md border px-3 py-1.5 text-sm"
      :class="[ l.active ? 'bg-primary text-primary-foreground border-primary' : 'hover:bg-muted',
                l.url === null ? 'pointer-events-none opacity-50' : '' ]"
    >
      <span v-if="l.label" v-html="l.label"></span>
    </Link>
  </nav>
</template>
