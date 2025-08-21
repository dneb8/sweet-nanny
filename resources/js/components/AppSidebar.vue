<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';

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
        title: 'Niñeras',
        href: '/#',
        icon: 'mdi:face-female-shimmer-outline',
    },
    {
        title: 'Tutores',
        href: '/#',
        icon: 'fluent-mdl2:family',
    },
];

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
                :adminItems="adminNavItems" 
            />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
