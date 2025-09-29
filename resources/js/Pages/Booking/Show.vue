<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { Button } from '@/components/ui/button'
import Heading from '@/components/Heading.vue'
import type { Booking } from '@/types/Booking'

defineProps<{
  booking: Booking
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
  <Head :title="`Reserva #${booking.id}`" />

  <!-- Breadcrumb -->
  <nav class="mb-3 text-sm text-muted-foreground">
    <Link :href="route('bookings.index')" class="hover:underline">Reservas</Link>
    <span class="mx-2">/</span>
    <span class="text-foreground font-medium">#{{ booking.id }}</span>
  </nav>

  <!-- Header -->
  <div class="mb-6 flex items-center justify-between">
    <Heading icon="ri:file-list-3-line" :title="`Reserva #${booking.id}`" />
    <div class="flex gap-2">
      <Link :href="route('bookings.edit', booking.id)">
        <Button variant="secondary" class="gap-2">
          <Icon icon="ri:edit-2-line" />
          Editar
        </Button>
      </Link>
      <!-- Ajusta acción de eliminar si tienes form -->
      <!-- <Button variant="destructive" class="gap-2"><Icon icon="ri:delete-bin-6-line" />Eliminar</Button> -->
    </div>
  </div>

  <!-- Grid principal -->
  <div class="grid gap-6 md:grid-cols-3">
    <!-- Columna izquierda: resumen -->
    <section class="rounded-lg border p-4 md:col-span-2">
      <h2 class="mb-3 text-base font-semibold">Resumen</h2>
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <div class="text-xs text-muted-foreground">Estado</div>
          <div>
            <span class="inline-flex rounded-full border px-2 py-0.5 text-xs">
              {{ booking.status ?? '—' }}
            </span>
          </div>
        </div>

        <div>
          <div class="text-xs text-muted-foreground">Creado</div>
          <div class="font-medium">{{ fmtDateTime(booking.created_at) }}</div>
        </div>

        <div>
          <div class="text-xs text-muted-foreground">Tutor</div>
          <div class="font-medium">
            {{ booking.tutor?.user.name ?? '—' }}
          </div>
          <div class="text-xs text-muted-foreground">
            {{ booking.tutor?.user.email ?? '' }}
          </div>
        </div>

        <div>
          <div class="text-xs text-muted-foreground">Niños</div>
          <div class="font-medium">
            {{ (booking.children ?? []).map(c => c.name).join(', ') || '—' }}
          </div>
        </div>

        <div class="sm:col-span-2">
          <div class="text-xs text-muted-foreground">Dirección</div>
          <div class="font-medium">
            {{ booking.address
                ? `${booking.address.street ?? ''} ${booking.address.ext_number ?? ''}, ${booking.address.neighborhood ?? ''}, ${booking.address.city ?? ''}, ${booking.address.state ?? ''}`
                : '—'
            }}
          </div>
        </div>
      </div>
    </section>

    <!-- Columna derecha: acciones rápidas -->
    <aside class="rounded-lg border p-4">
      <h2 class="mb-3 text-base font-semibold">Acciones rápidas</h2>
      <div class="flex flex-col gap-2">
        <Link :href="route('bookings.index')">
          <Button variant="outline" class="w-full gap-2">
            <Icon icon="ri:arrow-left-line" />
            Volver al listado
          </Button>
        </Link>
        <Link :href="route('bookings.edit', booking.id)">
          <Button class="w-full gap-2">
            <Icon icon="ri:edit-2-line" />
            Editar reserva
          </Button>
        </Link>
      </div>
    </aside>

    <!-- Lista de citas -->
    <section class="md:col-span-3 rounded-lg border p-4">
      <div class="mb-3 flex items-center justify-between">
        <h2 class="text-base font-semibold">Citas del servicio</h2>
        <span class="text-xs text-muted-foreground">
          Total: {{ booking.booking_appointments?.length ?? 0 }}
        </span>
      </div>

      <div v-if="(booking.booking_appointments ?? []).length === 0" class="rounded-md border p-4 text-sm text-muted-foreground">
        No hay citas registradas para esta reserva.
      </div>

      <div v-else class="grid gap-3">
        <div
          v-for="(appt, i) in booking.booking_appointments ?? []"
          :key="appt.id ?? i"
          class="grid grid-cols-1 gap-3 rounded-md border p-3 sm:grid-cols-5"
        >
          <div class="sm:col-span-2">
            <div class="text-xs text-muted-foreground">Fecha y hora</div>
            <div class="font-medium">
              {{ fmtDateTime(appt.start_date ?? appt.start_at) }} → {{ fmtDateTime(appt.end_date ?? appt.end_at) }}
            </div>
          </div>

          <div>
            <div class="text-xs text-muted-foreground">Niñera</div>
            <div class="font-medium">{{ appt.nanny?.name ?? '—' }}</div>
            <div class="text-xs text-muted-foreground">{{ appt.nanny?.email ?? '' }}</div>
          </div>

          <div>
            <div class="text-xs text-muted-foreground">Estado</div>
            <div class="font-medium">{{ appt.status ?? '—' }}</div>
          </div>

          <div>
            <div class="text-xs text-muted-foreground">Notas</div>
            <div class="line-clamp-3">{{ appt.notes ?? '—' }}</div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
