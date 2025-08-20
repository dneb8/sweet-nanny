<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from "@inertiajs/vue3"
import type { User } from '@/types/User'
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card'
import { Button } from "@/components/ui/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { ScrollArea, ScrollBar } from "@/components/ui/scroll-area"
import DeleteModal from '@/components/common/DeleteModal.vue'
import { MoreVertical } from 'lucide-vue-next'
import { Icon } from '@iconify/vue'

const props = defineProps<{
  user: User
}>()

const showDeleteModal = ref(false)

// Navegación
const showUser = () => router.get(route('users.show', props.user.ulid))
const editUser = () => router.get(route('users.edit', props.user.ulid))
const deleteUser = () => { showDeleteModal.value = true }
const confirmDeleteUser = () => router.delete(route('users.destroy', props.user.ulid))

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
</script>

<template>
  <Card class="relative overflow-hidden">
    <!-- Menú -->
    <div class="absolute top-2 right-2 z-20">
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <Button variant="ghost" size="icon" class="h-8 w-8 p-0">
            <MoreVertical class="h-4 w-4" />
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-44">
          <DropdownMenuGroup>
            <DropdownMenuItem @click="showUser" class="group text-primary hover:bg-muted">
              <Icon icon="mdi:account-eye-outline" class="w-4 h-4 mr-2 text-primary" />
              Ver perfil
            </DropdownMenuItem>
            <DropdownMenuItem @click="editUser" class="group text-sky-600 hover:bg-muted">
              <Icon icon="mdi:pencil-outline" class="w-4 h-4 mr-2 text-sky-600" />
              Editar
            </DropdownMenuItem>
          </DropdownMenuGroup>
          <DropdownMenuSeparator />
          <DropdownMenuItem @click="deleteUser" class="group text-rose-600 hover:bg-rose-50">
            <Icon icon="mdi:trash-can-outline" class="w-4 h-4 mr-2 text-rose-600" />
            Eliminar
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>

    <CardHeader class="flex flex-row gap-4 items-start p-4">
      <!-- Imagen + botón -->
      <div class="flex-none w-20 flex flex-col items-center">
        <div class="w-16 h-16 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center border overflow-hidden">
          <Icon icon="mdi:image-outline" class="w-8 h-8 text-slate-400" />
        </div>
        <Button @click="showUser" class="mt-4 h-7 px-2 text-xs">
          Ver perfil
        </Button>
      </div>

      <!-- Info usuario -->
      <div class="flex-1 min-w-0">
        <span
          :class="['inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium', getRoleBadgeClass(props.user.roles?.[0]?.name)]"
        >
          {{ props.user.roles?.[0]?.name ?? 'Sin rol' }}
        </span>

        <div class="mt-2 flex items-center gap-2 min-w-0">
          <h3 class="text-sm font-semibold truncate">
            {{ props.user.name }} {{ props.user.surnames }}
          </h3>
          <Icon v-if="props.user.email_verified_at" icon="mdi:check-circle" class="w-4 h-4 text-emerald-500" />
        </div>

        <p class="mt-1 text-xs text-muted-foreground truncate">{{ props.user.email }}</p>

        <!-- ScrollArea habilidades -->
        <div class="relative mt-3" ref="scrollContainer">
          <ScrollArea class="w-full whitespace-nowrap" data-scroll-content>
            <div class="flex gap-2">
              <span
                v-for="(skill, idx) in fakeSkills"
                :key="idx"
                class="flex-none text-xs px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-800"
              >
                {{ skill }}
              </span>
            </div>
            <ScrollBar orientation="horizontal" />
          </ScrollArea>
        </div>
      </div>
    </CardHeader>

    <!-- <CardContent />
    <CardFooter /> -->

    <DeleteModal v-model:show="showDeleteModal" :message="`¿Estás seguro de eliminar a ${props.user.name}?`" :onConfirm="confirmDeleteUser" />
  </Card>
</template>
