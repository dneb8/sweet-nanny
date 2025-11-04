<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();

const mainNavItems: NavItem[] = [
    {
        title: 'Inicio',
        href: '/dashboard',
        icon: 'proicons:home',
    },
];

const adminNavItems: NavItem[] = [
    {
        title: 'Usuarios',
        href: '/users',
        icon: 'proicons:person-multiple',
    },
    {
        title: 'Reseñas',
        href: '/reviews',
        icon: 'proicons:star',
    },
    // {
    //     title: 'Niñeras',
    //     href: '/nannies',
    //     icon: 'mdi:face-female-shimmer-outline',
    // },
    // {
    //     title: 'Tutores',
    //     href: '/#',
    //     icon: 'fluent-mdl2:family',
    // },
];

// Check if user has ADMIN role
const isAdmin = computed(() => {
    const user = page.props.auth?.user as { roles?: { name: string }[] } | null;
    const roleNames = user?.roles?.map(role => typeof role === 'string' ? role : role.name) ?? [];
    return roleNames.includes('admin');
});

// Check if user has TUTOR role
const isTutor = computed(() => {
    const user = page.props.auth?.user as { roles?: { name: string }[] } | null;
    const roleNames = user?.roles?.map(role => typeof role === 'string' ? role : role.name) ?? [];
    return roleNames.includes('tutor');
});

// Check if user has NANNY role
const isNanny = computed(() => {
    const user = page.props.auth?.user as { roles?: string[] } | null;
    return user?.roles?.includes('nanny') ?? false;
});

// Get current user's ID for profile link
const currentUserId = computed(() => {
    const user = page.props.auth?.user as { id?: number } | null;
    return user?.id;
});

// Bookings nav items - "Crear Servicio" only visible for tutors
const bookingsNavItems = computed(() => {
    const items: NavItem[] = [
        {
            title: 'Servicios',
            href: '/bookings',
            icon: 'ph:baby-carriage',
        },
    ];

    // Only tutors can see "Crear Servicio"
    if (isTutor.value) {
        items.push({
            title: 'Crear Servicio',
            href: '/bookings/create',
            icon: 'fluent:calendar-add-24-regular',
        });
    }

    // Admin and Nanny can see "Citas"
    if (isAdmin.value || isNanny.value) {
        items.push({
            title: 'Citas',
            href: '/booking-appointments',
            icon: 'ph:calendar-check',
        });
    }

    return items;
});

// Profile nav items - "Mi perfil público" only visible for tutors and nannies
const profileNavItems = computed(() => {
    const items: NavItem[] = [];

    // Only tutors and nannies can see "Mi perfil público"
    if ((isTutor.value || isNanny.value) && currentUserId.value) {
        items.push({
            title: 'Mi perfil público',
            href: `/users/${currentUserId.value}`,
            icon: 'proicons:person',
        });
    }

    return items;
});


const footerNavItems: NavItem[] = [
    {
        title: 'Ayuda',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: 'proicons:question-circle',
    },
];
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
