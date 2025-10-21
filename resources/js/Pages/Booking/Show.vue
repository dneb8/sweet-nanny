<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import Heading from '@/components/Heading.vue'
import type { Booking } from '@/types/Booking'
import { getQualityLabel } from '@/enums/quality.enum'
import { getCareerNameLabel } from '@/enums/careers/career-name.enum'
import { getCourseNameLabel } from '@/enums/courses/course-name.enum'

const props = defineProps<{
  booking: Booking
}>()

console.log('Booking props:', props.booking)

const fmtDateTime = (value?: string | Date | null) => {
  if (!value) return '—'
  const d = typeof value === 'string' ? new Date(value) : value
  return isNaN(+d) ? String(value) : new Intl.DateTimeFormat('es-MX', {
    dateStyle: 'medium',
    timeStyle: 'short'
  }).format(d)
}

const fmtDate = (value?: string | Date | null) => {
  if (!value) return '—'
  const d = typeof value === 'string' ? new Date(value) : value
  return isNaN(+d) ? String(value) : new Intl.DateTimeFormat('es-MX', {
    dateStyle: 'medium',
  }).format(d)
}

const getEnumLabel = (value: string, type: 'quality' | 'career' | 'course'): string => {
  try {
    if (type === 'quality') return getQualityLabel(value as any)
    if (type === 'career') return getCareerNameLabel(value as any)
    if (type === 'course') return getCourseNameLabel(value as any)
  } catch {
    return value
  }
  return value
}
</script>

<template>
  <Head :title="`Servicio #${booking.id}`" />

  <!-- Breadcrumb -->
  <nav class="mb-3 text-sm text-muted-foreground">
    <Link :href="route('bookings.index')" class="hover:underline">Servicios</Link>
    <span class="mx-2">/</span>
    <span class="text-foreground font-medium">#{{ booking.id }}</span>
  </nav>

  <!-- Header -->
  <div class="mb-6 flex items-center justify-between">
    <Heading icon="ri:file-list-3-line" :title="`Servicio #${booking.id}`" />
    <div class="flex gap-2">
      <Link :href="route('bookings.edit', booking.id)">
        <Button variant="secondary" class="gap-2">
          <Icon icon="ri:edit-2-line" />
          Editar
        </Button>
      </Link>
      <Button variant="destructive" class="gap-2"><Icon icon="ri:delete-bin-6-line" />Eliminar</Button>
    </div>
  </div>

  <!-- Grid principal -->
  <div class="grid gap-6 md:grid-cols-3">
    <!-- Columna izquierda: información principal -->
    <div class="space-y-6 md:col-span-2">
      <!-- Resumen -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Icon icon="lucide:file-text" class="h-5 w-5" />
            Resumen del Servicio
          </CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <div class="text-xs text-muted-foreground mb-1">Estado</div>
              <Badge variant="secondary">
                {{ booking.status ?? 'Pendiente' }}
              </Badge>
            </div>

            <div>
              <div class="text-xs text-muted-foreground mb-1">Creado</div>
              <div class="font-medium text-sm">{{ fmtDate(booking.created_at) }}</div>
            </div>

            <div>
              <div class="text-xs text-muted-foreground mb-1">Recurrente</div>
              <Badge :variant="booking.recurrent ? 'default' : 'outline'">
                <Icon :icon="booking.recurrent ? 'lucide:repeat' : 'lucide:calendar'" class="mr-1 h-3 w-3" />
                {{ booking.recurrent ? 'Sí' : 'No' }}
              </Badge>
            </div>

            <div>
              <div class="text-xs text-muted-foreground mb-1">Citas programadas</div>
              <div class="flex items-center gap-1 font-medium text-sm">
                <Icon icon="lucide:calendar-clock" class="h-4 w-4 text-muted-foreground" />
                {{ booking.booking_appointments?.length ?? 0 }}
              </div>
            </div>
          </div>

          <div v-if="booking.description" class="pt-2 border-t">
            <div class="text-xs text-muted-foreground mb-1">Descripción</div>
            <p class="text-sm">{{ booking.description }}</p>
          </div>
        </CardContent>
      </Card>

      <!-- Tutor -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Icon icon="lucide:user" class="h-5 w-5" />
            Tutor
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
              <Icon icon="lucide:user" class="h-5 w-5 text-primary" />
            </div>
            <div class="flex-1">
              <div class="font-medium">{{ booking.tutor?.user.name ?? '—' }}</div>
              <div class="text-sm text-muted-foreground">{{ booking.tutor?.user.email ?? '' }}</div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Niños -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Icon icon="lucide:baby" class="h-5 w-5" />
            Niños ({{ (booking.children ?? []).length }})
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="(booking.children ?? []).length === 0" class="text-sm text-muted-foreground">
            No hay niños asignados
          </div>
          <div v-else class="flex flex-wrap gap-2">
            <Badge v-for="child in booking.children" :key="child.id" variant="outline" class="gap-1">
              <Icon icon="lucide:baby" class="h-3 w-3" />
              {{ child.name }}
            </Badge>
          </div>
        </CardContent>
      </Card>

      <!-- Dirección -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Icon icon="lucide:map-pin" class="h-5 w-5" />
            Dirección del Servicio
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="!booking.address" class="text-sm text-muted-foreground">
            No se ha especificado dirección
          </div>
          <div v-else class="space-y-2">
            <div class="flex items-start gap-2">
              <Icon icon="lucide:home" class="mt-0.5 h-4 w-4 text-muted-foreground" />
              <div class="text-sm">
                <div class="font-medium">
                  {{ booking.address.street }} {{ booking.address.ext_number }}
                  <span v-if="booking.address.int_number">, Int. {{ booking.address.int_number }}</span>
                </div>
                <div class="text-muted-foreground">
                  {{ booking.address.neighborhood }}, {{ booking.address.city }}
                </div>
                <div class="text-muted-foreground">
                  {{ booking.address.state }}, {{ booking.address.postal_code }}
                </div>
              </div>
            </div>
            <Badge v-if="booking.address.type" variant="secondary" class="mt-2">
              {{ booking.address.type }}
            </Badge>
          </div>
        </CardContent>
      </Card>

      <!-- Requisitos de la Niñera -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Icon icon="lucide:clipboard-check" class="h-5 w-5" />
            Requisitos de la Niñera
          </CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
          <!-- Cualidades -->
          <div>
            <div class="text-xs text-muted-foreground mb-2">Cualidades</div>
            <div v-if="!booking.qualities || booking.qualities.length === 0" class="text-sm text-muted-foreground">
              No especificadas
            </div>
            <div v-else class="flex flex-wrap gap-2">
              <Badge v-for="quality in booking.qualities" :key="quality" variant="default">
                {{ getEnumLabel(quality, 'quality') }}
              </Badge>
            </div>
          </div>

          <!-- Carreras -->
          <div>
            <div class="text-xs text-muted-foreground mb-2">Carrera o Formación Académica</div>
            <div v-if="!booking.careers || booking.careers.length === 0" class="text-sm text-muted-foreground">
              No especificadas
            </div>
            <div v-else class="flex flex-wrap gap-2">
              <Badge v-for="career in booking.careers" :key="career" variant="secondary">
                <Icon icon="lucide:graduation-cap" class="mr-1 h-3 w-3" />
                {{ getEnumLabel(career, 'career') }}
              </Badge>
            </div>
          </div>

          <!-- Cursos -->
          <div>
            <div class="text-xs text-muted-foreground mb-2">Cursos Especializados</div>
            <div v-if="!booking.courses || booking.courses.length === 0" class="text-sm text-muted-foreground">
              No especificados
            </div>
            <div v-else class="flex flex-wrap gap-2">
              <Badge v-for="course in booking.courses" :key="course" variant="outline">
                <Icon icon="lucide:book-open" class="mr-1 h-3 w-3" />
                {{ getEnumLabel(course, 'course') }}
              </Badge>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Citas del Servicio -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Icon icon="lucide:calendar-days" class="h-5 w-5" />
              Citas Programadas
            </div>
            <Badge variant="secondary">
              {{ booking.booking_appointments?.length ?? 0 }}
            </Badge>
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="(booking.booking_appointments ?? []).length === 0" class="rounded-md border border-dashed p-6 text-center text-sm text-muted-foreground">
            <Icon icon="lucide:calendar-x" class="mx-auto mb-2 h-8 w-8 opacity-50" />
            No hay citas programadas para este servicio
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="(appt, i) in booking.booking_appointments ?? []"
              :key="appt.id ?? i"
              class="rounded-lg border p-4 space-y-3"
            >
              <div class="grid gap-3 sm:grid-cols-2">
                <div>
                  <div class="text-xs text-muted-foreground mb-1">Fecha y hora de inicio</div>
                  <div class="flex items-center gap-1 text-sm font-medium">
                    <Icon icon="lucide:calendar" class="h-4 w-4 text-muted-foreground" />
                    {{ fmtDateTime(appt.start_date ?? appt.start_at) }}
                  </div>
                </div>

                <div>
                  <div class="text-xs text-muted-foreground mb-1">Fecha y hora de fin</div>
                  <div class="flex items-center gap-1 text-sm font-medium">
                    <Icon icon="lucide:calendar-check" class="h-4 w-4 text-muted-foreground" />
                    {{ fmtDateTime(appt.end_date ?? appt.end_at) }}
                  </div>
                </div>

                <div v-if="appt.nanny">
                  <div class="text-xs text-muted-foreground mb-1">Niñera asignada</div>
                  <div class="text-sm font-medium">{{ appt.nanny.name }}</div>
                  <div class="text-xs text-muted-foreground">{{ appt.nanny.email }}</div>
                </div>

                <div>
                  <div class="text-xs text-muted-foreground mb-1">Estado</div>
                  <Badge variant="outline">{{ appt.status ?? 'Pendiente' }}</Badge>
                </div>
              </div>

              <div v-if="appt.notes" class="pt-3 border-t">
                <div class="text-xs text-muted-foreground mb-1">Notas</div>
                <p class="text-sm">{{ appt.notes }}</p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Columna derecha: acciones rápidas -->
    <aside class="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Icon icon="lucide:zap" class="h-5 w-5" />
            Acciones
          </CardTitle>
        </CardHeader>
        <CardContent class="space-y-2">
          <Link :href="route('bookings.edit', booking.id)" class="block">
            <Button variant="default" class="w-full justify-start gap-2">
              <Icon icon="lucide:edit" class="h-4 w-4" />
              Editar servicio
            </Button>
          </Link>
          <Link :href="route('bookings.index')" class="block">
            <Button variant="outline" class="w-full justify-start gap-2">
              <Icon icon="lucide:arrow-left" class="h-4 w-4" />
              Volver al listado
            </Button>
          </Link>
          <Button variant="destructive" class="w-full justify-start gap-2">
            <Icon icon="lucide:trash-2" class="h-4 w-4" />
            Eliminar
          </Button>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle class="text-sm">Información</CardTitle>
        </CardHeader>
        <CardContent class="space-y-3 text-sm">
          <div class="flex items-start gap-2">
            <Icon icon="lucide:info" class="mt-0.5 h-4 w-4 text-muted-foreground" />
            <div class="text-muted-foreground">
              Este servicio fue creado el {{ fmtDate(booking.created_at) }}
            </div>
          </div>
          <div v-if="booking.updated_at && booking.updated_at !== booking.created_at" class="flex items-start gap-2">
            <Icon icon="lucide:clock" class="mt-0.5 h-4 w-4 text-muted-foreground" />
            <div class="text-muted-foreground">
              Última modificación: {{ fmtDate(booking.updated_at) }}
            </div>
          </div>
        </CardContent>
      </Card>
    </aside>
  </div>
</template>
