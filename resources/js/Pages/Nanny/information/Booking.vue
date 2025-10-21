<script setup lang="ts">
import type { Nanny } from "@/types/Nanny"
import { ref, computed, Ref } from "vue"
import { Calendar } from "@/components/ui/calendar"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Icon } from "@iconify/vue"
import { parseDateTime, type DateValue } from "@internationalized/date"

const props = defineProps<{
  nanny: Nanny
  isOwner: boolean
}>()

// Paginación
const currentPage = ref(1)
const pageSize = 5

const totalPages = computed(() => {
  return Math.ceil((props.nanny.booking_appointments?.length ?? 0) / pageSize)
})

const paginatedServices = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return props.nanny.booking_appointments?.slice(start, start + pageSize) ?? []
})

// Servicio seleccionado → mostrará calendario
const selectedServiceId = ref<number | null>(null)
const selectedDate: Ref<DateValue | null> = ref(null)
const expandedService = ref<number | null>(null)

function selectService(service: any) {
  selectedServiceId.value = service.id
  expandedService.value = service.id
  selectedDate.value = parseDateTime(
    service.start_date.replace(" ", "T")
  ) as DateValue
}

// Mapear estatus a colores
const statusColors: Record<string, string> = {
  pending: "bg-yellow-500",
  unpaid: "bg-rose-500",
  paid: "bg-emerald-500",
  cancelled: "bg-gray-400",
}
</script>

<template>
  <Card class="bg-transparent border-none shadow-sm">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <Icon icon="lucide:calendar" />
        {{ isOwner ? "Mis servicios" : "Servicios" }}
      </CardTitle>
    </CardHeader>

    <CardContent>
      <div v-if="nanny.booking_appointments?.length">
        <!-- Layout responsivo -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Calendario -->
          <div class="flex justify-center">
            <Transition name="fade-slide" mode="out-in">
              <div v-if="selectedDate" key="calendar" class="w-full max-w-md m-auto">
                <h3 class="font-semibold mb-2 text-center">
                  Calendario del servicio #{{ selectedServiceId }}
                </h3>
                <Calendar v-model="selectedDate" class="m-auto" />
              </div>
              <div
                v-else
                key="no-calendar"
                class="text-muted-foreground text-center p-6 border rounded-lg w-full max-w-md"
              >
                Selecciona un servicio para ver su calendario
              </div>
            </Transition>
          </div>

          <!-- Lista de servicios -->
          <div class="space-y-3 lg:col-span-2">
              <div
                v-for="service in paginatedServices"
                :key="service.id"
                class="p-3 border rounded-lg cursor-pointer transition hover:bg-muted/40"
                @click="selectService(service)"
              >
                <div class="flex items-start gap-3">
                  <div
                    class="size-3 rounded-full mt-1.5"
                    :class="statusColors[service.status] ?? 'bg-gray-400'"
                  ></div>
                  <div class="flex-1">
                    <!-- Básico -->
                    <div class="font-semibold">
                      Servicio #{{ service.id }}
                    </div>
                    <div class="text-xs text-muted-foreground">
                      {{ service.start_date }} → {{ service.end_date }}
                    </div>
                    <div class="text-xs">
                      Estado: <span class="capitalize">{{ service.status }}</span>
                    </div>

                    <!-- Extra al seleccionar -->
                    <Transition name="expand">
                      <div
                        v-if="expandedService === service.id"
                        class="mt-2 space-y-1 text-sm"
                      >
                        <div class="text-muted-foreground">
                          {{ service.booking?.description ?? "Sin descripción" }}
                        </div>
                        <div>Costo: ${{ service.total_cost }}</div>
                      </div>
                    </Transition>
                  </div>
                </div>
              </div>

            <!-- Paginación -->
            <div class="flex justify-between items-center mt-4">
              <Button
                variant="outline"
                size="sm"
                :disabled="currentPage === 1"
                @click="currentPage--"
              >
                Anterior
              </Button>

              <span class="text-sm text-muted-foreground">
                Página {{ currentPage }} de {{ totalPages }}
              </span>

              <Button
                variant="outline"
                size="sm"
                :disabled="currentPage === totalPages"
                @click="currentPage++"
              >
                Siguiente
              </Button>
            </div>
          </div>
        </div>
      </div>

      <!-- Sin servicios -->
      <div v-else class="flex flex-col items-center text-muted-foreground py-6">
        <Icon icon="lucide:calendar-x" class="w-10 h-10 mb-2" />
        <span>No hay servicios agendados</span>
      </div>
    </CardContent>
  </Card>
</template>

<style scoped>
/* Transiciones */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}
.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(10px);
}
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.expand-enter-active,
.expand-leave-active {
  transition: all 0.25s ease;
}
.expand-enter-from,
.expand-leave-to {
  opacity: 0;
  max-height: 0;
}
.expand-enter-to,
.expand-leave-from {
  opacity: 1;
  max-height: 200px;
}

.list-move {
  transition: transform 0.3s ease;
}
</style>
