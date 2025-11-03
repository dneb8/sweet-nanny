<script setup lang="ts">
import { ref, computed } from "vue"
import { Calendar } from "@/components/ui/calendar"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Icon } from "@iconify/vue"
import { parseDateTime, type DateValue } from "@internationalized/date"

import type { BookingAppointment } from '@/types/BookingAppointment'
import { Nanny } from "@/types/Nanny"

const props = defineProps<{
  nanny: Nanny
  isOwner?: boolean
}>()

const services = computed<BookingAppointment[]>(() => props.nanny?.booking_appointments ?? [])

const selectedServiceId = ref<number | null>(null)
const selectedDate = ref<DateValue | null>(null)
const expandedService = ref<number | null>(null)

function toDateValue(dateTime: string | null | undefined): DateValue | null {
  if (!dateTime) return null
  // backend viene "YYYY-MM-DD HH:mm:ss" → Calendar pide "YYYY-MM-DDTHH:mm:ss"
  try {
    return parseDateTime(dateTime.replace(" ", "T")) as DateValue
  } catch {
    return null
  }
}

function selectService(service: BookingAppointment) {
  selectedServiceId.value = service.id
  expandedService.value = service.id
  selectedDate.value = toDateValue(service.start_date)
}

const statusColors: Record<string, string> = {
  pending: "bg-yellow-500",
  unpaid: "bg-rose-500",
  paid: "bg-emerald-500",
  cancelled: "bg-gray-400",
}
</script>

<template>
  <Card class="bg-white/10 border-none shadow-sm">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <Icon icon="lucide:calendar" />
        {{ isOwner ? "Mis servicios" : "Servicios" }}
      </CardTitle>
    </CardHeader>

    <CardContent>
      <div v-if="services.length">
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
              v-for="service in services"
              :key="service.id"
              class="p-3 border rounded-lg cursor-pointer transition hover:bg-muted/40"
              @click="selectService(service)"
            >
              <div class="flex items-start gap-3">
                <div
                  class="size-3 rounded-full mt-1.5"
                  :class="statusColors[service.status] ?? 'bg-gray-400'"
                />
                <div class="flex-1">
                  <div class="font-semibold">Servicio #{{ service.id }}</div>

                  <div class="text-xs text-muted-foreground">
                    {{ service.start_date }} → {{ service.end_date }}
                  </div>

                  <div class="text-xs">
                    Estado: <span class="capitalize">{{ service.status }}</span>
                  </div>

                  <Transition name="expand">
                    <div v-if="expandedService === service.id" class="mt-2 space-y-1 text-sm">
                      <div class="text-muted-foreground">
                        {{ service.booking?.description ?? "Sin descripción" }}
                      </div>
                    </div>
                  </Transition>
                </div>
              </div>
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