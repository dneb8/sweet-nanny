<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { computed } from 'vue';

interface User {
    name?: string;
    surnames?: string;
}

interface QuickLink {
    label: string;
    to: string;
    icon: string;
}

const page = usePage();
const user = computed<User>(() => page.props.auth?.user || {});

const links: QuickLink[] = [
    {
        label: 'Gestionar Usuarios',
        to: '/users',
        icon: 'lucide:users',
    },
    {
        label: 'Ver Bookings',
        to: '/bookings',
        icon: 'solar:calendar-outline',
    },
    {
        label: 'Gestionar Niñeras',
        to: '/nannies',
        icon: 'ph:user',
    },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <!-- Background with gradient and blobs -->
    <div class="relative min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-purple-50">
        <!-- Decorative blobs -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -left-10 -top-10 h-72 w-72 rounded-full bg-blue-200/30 blur-3xl"></div>
            <div class="absolute -right-10 top-40 h-96 w-96 rounded-full bg-purple-200/30 blur-3xl"></div>
            <div class="absolute bottom-10 left-1/3 h-80 w-80 rounded-full bg-pink-200/30 blur-3xl"></div>
        </div>

        <!-- Main content -->
        <div class="relative mx-auto max-w-6xl px-4 py-12">
            <!-- Glass panel -->
            <div class="rounded-3xl border border-white/40 bg-white/20 p-8 backdrop-blur-xl">
                <!-- Welcome section -->
                <div class="mb-8">
                    <p class="text-sm text-gray-500">Bienvenido(a)</p>
                    <h1 class="text-3xl font-semibold text-gray-800">
                        {{ user.name }} {{ user.surnames }}
                    </h1>
                </div>

                <!-- Quick links grid -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="link in links"
                        :key="link.label"
                        :href="link.to"
                        class="group relative block rounded-2xl border border-white/30 bg-white/15 p-5 backdrop-blur-xl transition-all duration-300 ease-out hover:scale-[1.02] hover:border-white/60 hover:shadow-[0_12px_50px_-15px_rgba(0,0,0,0.25)] focus:outline-none focus:ring-2 focus:ring-white/60"
                    >
                        <!-- Glass surface effect on hover -->
                        <div
                            class="pointer-events-none absolute inset-0 -z-10 rounded-2xl bg-white/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        ></div>

                        <!-- Content -->
                        <div class="flex items-center gap-3">
                            <Icon :icon="link.icon" class="h-5 w-5 text-gray-500 transition-colors group-hover:text-gray-600" />
                            <span class="text-base font-medium text-gray-700 transition-colors group-hover:text-gray-800">
                                {{ link.label }}
                            </span>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
