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

// Componentes
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
  forceShowTop3?: boolean
}>()

const showTop3Modal = ref(false)
const showDetailModal = ref(false)
const selectedNannyForDetail = ref<NannySelectionData | null>(null)
const searchTerm = ref('')
const availableNannies = ref<NannySelectionData[]>([])
const isLoading = ref(false)
const { toast } = useToast()

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
  {
    // showTop3Modal.value = true
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

/** IDs del Top 3 para resaltar y ordenar */
const topIds = computed(() => new Set((props.top3Nannies ?? []).map(n => n.id)))

/** Unión: top3 + disponibles, sin duplicados, orden: top3 primero */
const allNannies = computed<NannySelectionData[]>(() => {
  const byId = new Map<string, NannySelectionData>()
  ;(props.top3Nannies ?? []).forEach(n => n?.id && byId.set(n.id, n))
  ;(availableNannies.value ?? []).forEach(n => n?.id && !byId.has(n.id) && byId.set(n.id, n))
  const list = Array.from(byId.values())
  const idsTop = topIds.value
  return list.sort((a, b) => {
    const aTop = idsTop.has(a.id)
    const bTop = idsTop.has(b.id)
    return aTop === bTop ? 0 : aTop ? -1 : 1
  })
})

/** Filtro por búsqueda sobre la lista unificada */
const filteredNannies = computed(() => {
  const term = (searchTerm.value || '').toLowerCase().trim()
  if (!term) return allNannies.value
  return allNannies.value.filter(n => n.name?.toLowerCase().includes(term))
})

/** Helper: clases de “brillo” para top 3 */
function topHighlightClasses(isTop: boolean) {
  if (!isTop) return ''
  // Glow suave + anillo + animación sutil
  return [
    'relative',
    'rounded-2xl',
    'shadow-[0_0_0_0_rgba(244,63,94,0.0)]',
    'before:absolute before:-inset-1 before:rounded-3xl',
    'before:bg-gradient-to-r before:from-fuchsia-400/25 before:via-rose-300/20 before:to-purple-400/25',
    'before:blur-xl before:content-[""]',
    'after:pointer-events-none',
    // Pulso muy sutil (custom): usa animate-pulse si prefieres algo estándar
    'animate-[glow_2.8s_ease-in-out_infinite]',
  ].join(' ')
}
</script>

<style scoped>
@keyframes glow {
  0%, 100% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0.25); transform: translateZ(0) scale(1);}
  50% { box-shadow: 0 0 40px 6px rgba(244, 63, 94, 0.35);  transform: translateZ(0) scale(1.02);  }
}
</style>

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
        <div class="flex items-center gap-2">
          <!-- Botón para reabrir Top 3 manualmente -->
          <button
            @click="showTop3Modal = true"
            class="group relative overflow-hidden origin-left h-16 w-80 border bg-background text-left p-3 pr-5 rounded-lg
                   text-base font-bold text-gray-500 dark:text-gray-50
                   underline underline-offset-2 hover:underline hover:underline-offset-4 hover:decoration-2 hover:text-rose-300
                   duration-500 hover:duration-500 before:duration-500 after:duration-500 group-hover:before:duration-500 group-hover:after:duration-500
                   hover:border-rose-300 hover:before:[box-shadow:_20px_20px_20px_30px_#a21caf]
                   hover:before:-bottom-8 hover:before:right-12 hover:after:-right-8
                   before:absolute before:z-10 before:content-[''] before:w-12 before:h-12 before:right-1 before:top-1 before:bg-violet-500 before:rounded-full before:blur-lg
                   after:absolute after:z-10 after:content-[''] after:w-20 after:h-20 after:right-8 after:top-3 after:bg-rose-300 after:rounded-full after:blur-lg"
          >
            <span class="relative z-20">Ver Mejores coincidencias</span>
          </button>
        </div>
      </header>

      <!-- Top 3 (controlado SOLO por el padre) -->
      <Top3Dialog
        v-model:open="showTop3Modal"
        :top3="props.top3Nannies"
        :qualities="props.qualities"
        @close="closeTop3"
        @see="openDetail"
        @choose="assignNanny"
      />

      <!-- Dialog de Detalle -->
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
            <div
              v-for="nanny in filteredNannies"
              :key="nanny.id"
              :class="topHighlightClasses(topIds.has(nanny.id))"
            >
              <NannyCard
                :nanny="nanny"
                :qualities="props.qualities"
                :careers="props.careers"
                @see="openDetail"
                @choose="(n: NannySelectionData) => assignNanny(n.id)"
              />
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>