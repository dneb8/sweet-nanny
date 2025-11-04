<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const quickActions = [
    {
        title: 'Crear Servicio',
        description: 'Solicita una nueva niñera',
        icon: 'mdi:calendar-plus',
        href: '/bookings/create',
        color: 'text-emerald-600 dark:text-emerald-400',
        bgColor: 'bg-emerald-50 dark:bg-emerald-950/30',
    },
    {
        title: 'Mis Servicios',
        description: 'Ver servicios activos y pasados',
        icon: 'mdi:calendar-account',
        href: '/bookings',
        color: 'text-blue-600 dark:text-blue-400',
        bgColor: 'bg-blue-50 dark:bg-blue-950/30',
    },
    {
        title: 'Mi Perfil',
        description: 'Editar información personal',
        icon: 'mdi:account-cog',
        href: '/settings/profile',
        color: 'text-purple-600 dark:text-purple-400',
        bgColor: 'bg-purple-50 dark:bg-purple-950/30',
    },
];

const stats = [
    {
        label: 'Servicios Activos',
        icon: 'mdi:calendar-check',
        color: 'text-emerald-600 dark:text-emerald-400',
    },
    {
        label: 'Servicios Completados',
        icon: 'mdi:calendar-star',
        color: 'text-blue-600 dark:text-blue-400',
    },
    {
        label: 'Niñeras Favoritas',
        icon: 'mdi:heart',
        color: 'text-pink-600 dark:text-pink-400',
    },
];
</script>

<template>
    <Head title="Tutor Dashboard" />

    <div class="space-y-8">
        <!-- Welcome Section -->
        <div class="flex flex-col gap-3">
            <div class="flex items-center gap-3">
                <div
                    class="flex size-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg"
                >
                    <Icon icon="mdi:human-male-female-child" class="size-7 text-white" />
                </div>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-50">
                        Bienvenido, {{ user?.name }} 👋
                    </h1>
                    <div class="flex items-center gap-2">
                        <Badge variant="secondary" class="mt-1">
                            <Icon icon="mdi:account-heart" class="mr-1 size-3" />
                            Tutor
                        </Badge>
                    </div>
                </div>
            </div>
            <p class="text-base text-muted-foreground">
                Administra tus servicios de cuidado infantil y encuentra las mejores niñeras para tu familia.
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid gap-4 md:grid-cols-3">
            <Card v-for="stat in stats" :key="stat.label" class="transition-all hover:shadow-md">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">{{ stat.label }}</CardTitle>
                    <div class="rounded-lg bg-muted p-2">
                        <Icon :icon="stat.icon" :class="['size-4', stat.color]" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">--</div>
                    <p class="text-xs text-muted-foreground">Disponible próximamente</p>
                </CardContent>
            </Card>
        </div>

        <!-- Quick Actions -->
        <div class="space-y-4">
            <div class="flex items-center gap-2">
                <Icon icon="mdi:lightning-bolt" class="size-5 text-amber-500" />
                <h2 class="text-xl font-semibold">Acciones Rápidas</h2>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <Link v-for="action in quickActions" :key="action.title" :href="action.href">
                    <Card
                        class="group cursor-pointer transition-all hover:shadow-md hover:shadow-primary/5 hover:-translate-y-0.5"
                    >
                        <CardHeader>
                            <div class="flex items-start gap-4">
                                <div
                                    :class="[
                                        'rounded-xl p-3 transition-colors group-hover:scale-110',
                                        action.bgColor,
                                    ]"
                                >
                                    <Icon :icon="action.icon" :class="['size-6', action.color]" />
                                </div>
                                <div class="flex-1 space-y-1">
                                    <CardTitle class="text-base">{{ action.title }}</CardTitle>
                                    <CardDescription class="text-sm">{{ action.description }}</CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                    </Card>
                </Link>
            </div>
        </div>

        <!-- Upcoming Bookings -->
        <Card>
            <CardHeader>
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle>Próximas Citas</CardTitle>
                        <CardDescription>Tus servicios programados</CardDescription>
                    </div>
                    <Button variant="ghost" size="sm">
                        Ver todo
                        <Icon icon="mdi:arrow-right" class="ml-2 size-4" />
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <Icon icon="mdi:calendar-blank-outline" class="mb-4 size-12 text-muted-foreground/50" />
                    <p class="text-sm text-muted-foreground">No tienes servicios programados</p>
                    <Link href="/bookings/create">
                        <Button variant="outline" size="sm" class="mt-4">
                            <Icon icon="mdi:plus" class="mr-2 size-4" />
                            Crear Servicio
                        </Button>
                    </Link>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
