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
            />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
