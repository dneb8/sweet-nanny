<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { computed } from 'vue'
import Badge from '@/components/common/Badge.vue' // ajusta la ruta si tu Badge vive en otro sitio

interface User { name?: string; surnames?: string }
interface QuickLink { label: string; to: string; icon: string; badge?: string }

const page = usePage()
const user = computed<User>(() => (page.props.auth?.user as User) ?? {})

// Enlaces: Inicio, Crear servicio, Ajustes
const links: QuickLink[] = [
  { label: 'Inicio',          to: '/',                 icon: 'lucide:home' },
  { label: 'Crear servicio',  to: '/bookings/create',  icon: 'lucide:plus-circle' },
  { label: 'Ajustes',         to: '/settings/profile', icon: 'lucide:settings' },
]

// Clases para el rol TUTOR (sky) tal como pediste
const tutorRoleClasses =
  'w-fit border mt-2 bg-sky-200/70 text-sky-500 dark:bg-sky-400/25 dark:border-sky-400 dark:text-sky-200'
</script>

<template>
  <Head title="Tutor Dashboard" />

  <!-- contenedor más angosto -->
  <div class="mx-auto max-w-4xl px-4 py-10">
    <!-- saludo minimal -->
    <div class="mb-8">
      <p class="text-sm text-gray-600 dark:text-gray-400/90">Bienvenido(a)</p>
      <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">
        {{ user.name }} {{ user.surnames }}
      </h1>
      <Badge
        :class="tutorRoleClasses"
        :label="'Tutor'"
      />
    </div>

    <!-- grid de cards con altura uniforme -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <Link
        v-for="link in links"
        :key="link.to"
        :href="link.to"
        class="group relative block overflow-hidden rounded-2xl
               border border-black/5 bg-white/60 backdrop-blur-xl p-5
               transition-all duration-300 ease-out
               hover:scale-[1.02] hover:shadow-md
               focus:outline-none focus:ring-2 focus:ring-sky-300
               dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10
               h-36"
      >
        <div class="flex h-full items-start justify-between">
          <!-- texto + icono -->
          <div class="flex flex-col gap-2">
            <div class="flex items-center gap-3">
              <Icon
                :icon="link.icon"
                class="h-5 w-5 text-gray-600 transition-colors
                       group-hover:text-gray-800
                       dark:text-gray-300 dark:group-hover:text-gray-100"
              />
              <span
                class="text-base font-medium
                       text-gray-800 group-hover:text-gray-900
                       dark:text-gray-100 dark:group-hover:text-white"
              >
                {{ link.label }}
              </span>
            </div>
            <!-- sin badge opcional -->
          </div>
        </div>
      </Link>
    </div>
  </div>
</template>
