<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Icon } from '@iconify/vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import type { Booking } from '@/types/Booking'
import type { BookingAppointment } from '@/types/BookingAppointment'
import type { NannySelectionData } from '@/types/Nanny'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { useToast } from '@/composables/useToast'

// Nuevos componentes
import Top3Dialog from './components/Top3Dialog.vue'
import NannyCard from './components/NannyCard.vue'
import NannyDetailDialog from './components/NannyDetailDialog.vue'

const props = defineProps<{
  booking: Booking
  appointment: BookingAppointment
  top3Nannies: NannySelectionData[]
  qualities: Record<string, string>
  careers: Record<string, string>
  courseNames: Record<string, string>
}>()

const showTop3Modal = ref(false)
const showDetailModal = ref(false)
const selectedNannyForDetail = ref<NannySelectionData | null>(null)
const searchTerm = ref('')
const availableNannies = ref<NannySelectionData[]>([])
const isLoading = ref(false)
const { toast } = useToast()

const top3Key = `sn_top3_shown_b${props.booking.id}_a${props.appointment.id}`

const fetchNannies = async () => {
  isLoading.value = true
  try {
    const { data } = await axios.get(
      route('api.bookings.appointments.nannies.available', {
        booking: props.booking.id,
        appointment: props.appointment.id,
        searchTerm: searchTerm.value,
      })
    )
    availableNannies.value = data?.data || []
  } catch (error) {
    console.error('Error fetching niñeras:', error)
    toast({
      title: 'Error',
      description: 'No se pudieron cargar las niñeras disponibles. Intenta de nuevo.',
    })
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  const alreadyShown = typeof window !== 'undefined' && sessionStorage.getItem(top3Key) === '1'
  if (!alreadyShown && (props.top3Nannies?.length ?? 0) > 0) {
    showTop3Modal.value = true
    sessionStorage.setItem(top3Key, '1')
  }
  fetchNannies()
})

const openDetail = (nanny: NannySelectionData) => {
  selectedNannyForDetail.value = nanny
  showDetailModal.value = true
}

const closeDetail = () => {
  showDetailModal.value = false
  selectedNannyForDetail.value = null
}

const closeTop3 = () => {
  showTop3Modal.value = false
  if (typeof window !== 'undefined') sessionStorage.setItem(top3Key, '1')
}

const assignNanny = (nannyId: string) => {
  router.post(
    route('bookings.appointments.nannies.assign', {
      booking: props.booking.id,
      appointment: props.appointment.id,
      nanny: nannyId,
    }),
    {},
    { onSuccess: () => {} }
  )
}
const formatDate = (dateString: string) =>
  new Date(dateString).toLocaleDateString('es-MX', { year: 'numeric', month: 'short', day: 'numeric' })

const filteredNannies = computed(() => {
  if (!searchTerm.value) return availableNannies.value
  const term = searchTerm.value.toLowerCase()
  return availableNannies.value.filter(n => n.name.toLowerCase().includes(term))
})
</script>

<template>
  <div class="min-h-[calc(100vh-80px)]">
    <div class="px-3 sm:px-6 py-6 sm:py-8">
      <!-- Header -->
      <header
        class="rounded-3xl p-4 sm:p-6 flex gap-3 flex-col sm:flex-row items-start sm:items-center justify-between backdrop-blur-xl border border-white/30 dark:border-white/10 shadow-lg bg-rose-50/30 dark:bg-rose-800/5"
      >
        <div>
          <h1 class="text-xl sm:text-2xl font-semibold tracking-tight">Elegir niñera</h1>
          <p class="text-sm text-muted-foreground mt-1">
            Cita: {{ formatDate(props.appointment.start_date) }} - {{ formatDate(props.appointment.end_date) }}
          </p>
        </div>
        <Button variant="outline" size="sm" @click="router.get(route('bookings.show', props.booking.id))">
          <Icon icon="lucide:arrow-left" class="mr-2 h-4 w-4" />
          Volver
        </Button>
      </header>

      <!-- Top 3 (componente) -->
      <Top3Dialog
        v-model:open="showTop3Modal"
        :top3="props.top3Nannies"
        :qualities="props.qualities"
        @close="closeTop3"
        @see="openDetail"
        @choose="assignNanny"
      />

      <!-- Dialog de Detalle (componente) -->
      <NannyDetailDialog
        v-model:open="showDetailModal"
        :nanny="selectedNannyForDetail"
        :qualities="props.qualities"
        :careers="props.careers"
        :course-names="props.courseNames"
        @close="closeDetail"
        @choose="(n: NannySelectionData) => assignNanny(n.id)"
      />

      <!-- Main content -->
      <section class="mt-6">
        <div class="rounded-3xl border border-white/30 bg-white/20 dark:border-white/10 dark:bg-white/5 backdrop-blur-xl shadow-lg p-4 sm:p-6">
          <div class="mb-4 flex items-center gap-4">
            <div class="flex-1">
              <Input v-model="searchTerm" placeholder="Buscar niñera por nombre..." class="max-w-md" />
            </div>
            <Button variant="outline" size="sm" @click="fetchNannies">
              <Icon icon="lucide:refresh-cw" class="h-4 w-4" :class="{ 'animate-spin': isLoading }" />
            </Button>
          </div>

          <div v-if="isLoading" class="text-center py-12">
            <Icon icon="lucide:loader-2" class="mx-auto h-8 w-8 animate-spin text-muted-foreground" />
            <p class="mt-2 text-sm text-muted-foreground">Cargando niñeras disponibles...</p>
          </div>

          <div v-else-if="filteredNannies.length === 0" class="text-center py-12 text-muted-foreground">
            <Icon icon="lucide:user-x" class="mx-auto h-12 w-12 mb-3 opacity-50" />
            <p class="font-medium">Sin niñeras disponibles en ese horario</p>
            <p class="text-sm mt-2">Intenta ajustar la hora o vuelve más tarde</p>
          </div>

          <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <NannyCard
              v-for="nanny in filteredNannies"
              :key="nanny.id"
              :nanny="nanny"
              :qualities="props.qualities"
              :careers="props.careers"
              @see="openDetail"
              @choose="(n: NannySelectionData) => assignNanny(n.id)"
            />
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
