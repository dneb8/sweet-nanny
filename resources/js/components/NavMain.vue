<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    items: NavItem[];
    adminItems?: NavItem[];
    bookingsItems?: NavItem[];
    profileItems?: NavItem[];
}>();

const page = usePage();
</script>

<template>
    <SidebarGroup class="pl-4 py-0">
        <SidebarGroupLabel> Menú</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                    <Link :href="item.href">                     
                        <Icon :icon="item.icon" width="24" height="24"/>
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
    <SidebarGroup v-if="adminItems && adminItems.length > 0" class="pl-4 py-0">
        <SidebarGroupLabel>Administración</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in adminItems" :key="item.title">
                <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                    <Link :href="item.href">              
                        <Icon :icon="item.icon" width="24" height="24"/>
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
    <SidebarGroup class="pl-4 py-0">
        <SidebarGroupLabel>Servicios</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in bookingsItems" :key="item.title">
                <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                    <Link :href="item.href">              
                        <Icon :icon="item.icon" width="24" height="24"/>
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
    <SidebarGroup v-if="profileItems && profileItems.length > 0" class="pl-4 py-0">
        <SidebarGroupLabel>Perfil</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in profileItems" :key="item.title">
                <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                    <Link :href="item.href">              
                        <Icon :icon="item.icon" width="24" height="24"/>
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
