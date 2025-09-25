import { ref, onMounted, onUnmounted } from 'vue'
import { router } from "@inertiajs/vue3"
import type { Booking } from '@/types/Booking'

export function useBookingAppointment(booking: Booking) {
  const showDeleteModal = ref(false)

  // 🔹 Navegación
  const showBooking = () => router.get(route('bookings.show', booking.id))
  const editBooking = () => router.get(route('bookings.edit', booking.id))
  const deleteBooking = () => { showDeleteModal.value = true }
  const confirmDeleteBooking = () => router.delete(route('bookings.destroy', booking.id))

  // 🔹 Estados ficticios (puedes mapear con tus enums reales en DB)
  const statusLabels: Record<string, string> = {
    pending: "Pendiente",
    confirmed: "Confirmada",
    completed: "Completada",
    cancelled: "Cancelada",
  }

  const paymentStatusLabels: Record<string, string> = {
    unpaid: "No pagado",
    paid: "Pagado",
    refunded: "Reembolsado",
  }

  // 🔹 Scroll automático "ping-pong" para mostrar servicios relacionados
  let scrollInterval: number | null = null
  const scrollContainer = ref<HTMLElement | null>(null)
  let scrollDirection = 1 // 1: derecha, -1: izquierda

  onMounted(() => {
    const el = scrollContainer.value?.querySelector('[data-scroll-content]') as HTMLElement
    if (!el) return

    scrollInterval = window.setInterval(() => {
      if (el.scrollLeft + el.clientWidth >= el.scrollWidth) {
        scrollDirection = -1
      } else if (el.scrollLeft <= 0) {
        scrollDirection = 1
      }
      el.scrollLeft += scrollDirection * 0.5 // velocidad muy suave
    }, 16) // ~60fps
  })

  onUnmounted(() => {
    if (scrollInterval) clearInterval(scrollInterval)
  })

  // 🔹 Badges por estado
  const getStatusBadgeClass = (status?: string) => {
    switch (status) {
      case 'pending':
        return 'bg-yellow-200/70 text-yellow-700 dark:bg-yellow-400/50 dark:text-yellow-200'
      case 'confirmed':
        return 'bg-sky-200/70 text-sky-600 dark:bg-sky-400/50 dark:text-sky-200'
      case 'completed':
        return 'bg-emerald-200/70 text-emerald-600 dark:bg-emerald-400/50 dark:text-emerald-200'
      case 'cancelled':
        return 'bg-red-200/70 text-red-600 dark:bg-red-400/50 dark:text-red-200'
      default:
        return 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200'
    }
  }

  const getPaymentBadgeClass = (paymentStatus?: string) => {
    switch (paymentStatus) {
      case 'unpaid':
        return 'bg-rose-200/70 text-rose-600 dark:bg-rose-400/50 dark:text-rose-200'
      case 'paid':
        return 'bg-green-200/70 text-green-600 dark:bg-green-400/50 dark:text-green-200'
      case 'refunded':
        return 'bg-indigo-200/70 text-indigo-600 dark:bg-indigo-400/50 dark:text-indigo-200'
      default:
        return 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200'
    }
  }

  return {
    showDeleteModal,
    showBooking,
    editBooking,
    deleteBooking,
    confirmDeleteBooking,
    statusLabels,
    paymentStatusLabels,
    getStatusBadgeClass,
    getPaymentBadgeClass,
    scrollContainer,
  }
}
