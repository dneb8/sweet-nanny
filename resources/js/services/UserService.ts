// services/UserService.ts
import { ref, type Ref } from 'vue'
import { router } from '@inertiajs/vue3'
import type { User } from '@/types/User'
import { RoleEnum } from '@/enums/role.enum'

export class UserService {
  private user: User
  public showDeleteModal = ref(false)

  // --- Auto-scroll state ---
  private scrollInterval: number | null = null
  private scrollDirection = 1 // 1: derecha, -1: izquierda
  public scrollContainer: Ref<HTMLElement | null> = ref(null)

  constructor(user: User) {
    this.user = user
  }

  // --- Navegación ---
  public showUser = () => router.get(route('users.show', this.user.ulid))
  public editUser = () => router.get(route('users.edit', this.user.ulid))
  public deleteUser = () => { this.showDeleteModal.value = true }
  public confirmDeleteUser = () => router.delete(route('users.destroy', this.user.ulid))

  // --- Badges por rol ---
  public getRoleBadgeClass(role?: RoleEnum): string {
    if (!role) return ''
    const classes: Record<RoleEnum, string> = {
      [RoleEnum.ADMIN]: 'bg-emerald-200/70 text-emerald-500 dark:bg-emerald-400/25 dark:border dark:border-emerald-400 dark:text-emerald-200',
      [RoleEnum.NANNY]: 'bg-pink-200/70 text-pink-500 dark:bg-pink-400/25 dark:border dark:border-pink-400 dark:text-pink-200',
      [RoleEnum.TUTOR]: 'bg-sky-200/70 text-sky-500 dark:bg-sky-400/25 dark:border dark:border-sky-400 dark:text-sky-200',
    }
    return classes[role] ?? ''
  }

  // --- Auto-scroll "ping-pong" ---
  /**
   * Llama esto cuando ya exista el DOM del contenedor.
   * Si no pasas `el`, usará this.scrollContainer.value
   */
  public startAutoScroll(el?: HTMLElement) {
    const container = el ?? this.scrollContainer.value
    if (!container) return

    // El slot/cuerpo que realmente scrollea (si usas un hijo)
    const scroller = container.querySelector<HTMLElement>('[data-scroll-content]') ?? container
    if (!scroller) return

    this.stopAutoScroll() // evita dobles intervalos
    this.scrollInterval = window.setInterval(() => {
      // límites → invertir dirección
      if (scroller.scrollLeft + scroller.clientWidth >= scroller.scrollWidth) {
        this.scrollDirection = -1
      } else if (scroller.scrollLeft <= 0) {
        this.scrollDirection = 1
      }
      scroller.scrollLeft += this.scrollDirection * 0.5 // velocidad suave
    }, 16) // ~60 fps
  }

  /** Llama esto en onUnmounted o cuando escondas el componente */
  public stopAutoScroll() {
    if (this.scrollInterval) {
      clearInterval(this.scrollInterval)
      this.scrollInterval = null
    }
  }
}
