<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue'
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem
} from '@/components/ui/sidebar'
import { type NavItem } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLogo from './AppLogo.vue'

type RoleLike = { name: string } | string
type UserLike = {
  id?: number
  roles?: RoleLike[]
  tutor?: { ulid?: string } | null
  nanny?: { ulid?: string } | null
}

const page = usePage()
const user = computed(() => (page.props.auth?.user ?? {}) as UserLike)

const roleNames = computed(() =>
  (user.value.roles ?? []).map(r => (typeof r === 'string' ? r : r.name))
)

const isAdmin = computed(() => roleNames.value.includes('admin'))
const isTutor = computed(() => roleNames.value.includes('tutor'))
const isNanny = computed(() => roleNames.value.includes('nanny'))

const mainNavItems: NavItem[] = [
  { title: 'Inicio', href: '/dashboard', icon: 'proicons:home' },
]

const adminNavItems: NavItem[] = [
  { title: 'Usuarios', href: '/users', icon: 'proicons:person-multiple' },
  { title: 'Reseñas', href: '/reviews', icon: 'proicons:star' },
]

/**
 * Bookings nav items
 * - "Servicios" solo si es admin o tutor (en tutor se llama "Mis servicios")
 * - "Citas" (/booking-appointments) solo si es tutor o nanny
 */
const bookingsNavItems = computed<NavItem[]>(() => {
  const items: NavItem[] = []

  // Servicios / Mis servicios
  if (isAdmin.value || isTutor.value) {
    items.push({
      title: isTutor.value ? 'Mis servicios' : 'Servicios',
      href: '/bookings',
      icon: 'ph:baby-carriage',
    })
  }

  // Citas (para tutor y nanny)
  if (isTutor.value || isNanny.value) {
    items.push({
      title: 'Citas',
      href: '/booking-appointments',
      icon: 'fluent:calendar-24-regular', // calendario
    })
  }

  // "Crear Servicio" solo visible para tutor
  if (isTutor.value) {
    items.push({
      title: 'Crear Servicio',
      href: '/bookings/create',
      icon: 'fluent:calendar-add-24-regular',
    })
  }

  return items
})

// Profile nav items - “Mi perfil público” directo a tutors/nannies por ULID
const profileNavItems = computed<NavItem[]>(() => {
  const items: NavItem[] = []
  if (isTutor.value) {
    items.push({
      title: 'Mi perfil público',
      href: `/tutors/${user.value.tutor?.ulid}`,
      icon: 'proicons:person',
    })
  }
  if (isNanny.value) {
    items.push({
      title: 'Mi perfil público',
      href: `/nannies/${user.value.nanny?.ulid}`,
      icon: 'proicons:person',
    })
  }
  return items
})

const footerNavItems: NavItem[] = [
  {
    title: 'Ayuda',
    href: 'https://laravel.com/docs/starter-kits#vue',
    icon: 'proicons:question-circle',
  },
]
</script>

<template>
  <Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <AppLogo />
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
      <NavMain
        :items="mainNavItems"
        :adminItems="isAdmin ? adminNavItems : undefined"
        :bookingsItems="bookingsNavItems"
        :profileItems="profileNavItems"
      />
    </SidebarContent>

    <SidebarFooter>
      <NavFooter :items="footerNavItems" />
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>
