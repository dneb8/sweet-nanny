<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    items: NavItem[];
    adminItems?: NavItem[];
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
    <SidebarGroup class="pl-4 py-0">
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
</template>
