import { ref, onMounted, onUnmounted } from 'vue'
import { router } from "@inertiajs/vue3"
import type { User } from '@/types/User'

export function useUserService(user: User) {
  const showDeleteModal = ref(false)

  // Navegación
  const showUser = () => router.get(route('users.show', user.ulid))
  const editUser = () => router.get(route('users.edit', user.ulid))
  const deleteUser = () => { showDeleteModal.value = true }
  const confirmDeleteUser = () => router.delete(route('users.destroy', user.ulid))

  // Tags ficticias
  const fakeSkills = [
    'Cuidado infantil',
    'Primeros auxilios',
    'Limpieza ligera',
    'Cocina básica',
    'Puntual',
    'Habla inglés',
    'Manejo de niños pequeños'
  ]

  // Scroll automático "ping-pong"
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

  // Color de badge según rol
  const getRoleBadgeClass = (role?: string) => {
    switch (role?.toLowerCase()) {
      case 'nanny':
        return 'bg-pink-200/70 text-pink-500 dark:bg-pink-400/50 dark:border dark:border-pink-400 dark:text-pink-200'
      case 'tutor':
        return 'bg-sky-200/70 text-sky-500 dark:bg-sky-400/50 dark:border dark:border-sky-400 dark:text-sky-200'
      case 'admin':
        return 'bg-emerald-200/70 text-emerald-500 dark:bg-emerald-400/50 dark:border dark:border-emerald-400 dark:text-emerald-200'
      default:
        return 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200'
    }
  }

  return {
    showDeleteModal,
    fakeSkills,
    showUser,
    editUser,
    deleteUser,
    confirmDeleteUser,
    getRoleBadgeClass,
    scrollContainer,
  }
}
