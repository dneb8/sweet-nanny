<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import type { Booking } from '@/types/Booking';
import type { BookingAppointment } from '@/types/BookingAppointment';
import type { NannySelectionData } from '@/types/Nanny';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useToast } from '@/composables/useToast';

const props = defineProps<{
    booking: Booking;
    appointment: BookingAppointment;
    top3Nannies: NannySelectionData[];
    qualities: Record<string, string>;
    careers: Record<string, string>;
    courseNames: Record<string, string>;
}>();

const showTop3Modal = ref(true);
const showDetailModal = ref(false);
const selectedNannyForDetail = ref<NannySelectionData | null>(null);
const searchTerm = ref('');
const availableNannies = ref<NannySelectionData[]>([]);
const isLoading = ref(false);
const { toast } = useToast();

// Fetch available nannies from API
const fetchNannies = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(
            route('api.bookings.appointments.nannies.available', {
                booking: props.booking.id,
                appointment: props.appointment.id,
                searchTerm: searchTerm.value,
            })
        );
        availableNannies.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching nannies:', error);
        toast({
            title: 'Error',
            description: 'No se pudieron cargar las niñeras disponibles. Por favor, intenta de nuevo.',
            variant: 'destructive',
        });
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchNannies();
});

const closeTop3 = () => {
    showTop3Modal.value = false;
};

const openDetail = (nanny: NannySelectionData) => {
    selectedNannyForDetail.value = nanny;
    showDetailModal.value = true;
};

const closeDetail = () => {
    showDetailModal.value = false;
    selectedNannyForDetail.value = null;
};

const assignNanny = (nannyId: string) => {
    router.post(
        route('bookings.appointments.nannies.assign', {
            booking: props.booking.id,
            appointment: props.appointment.id,
            nanny: nannyId,
        }),
        {},
        {
            onSuccess: () => {
                // Toast will be handled by flash message
            },
        }
    );
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const filteredNannies = computed(() => {
    if (!searchTerm.value) return availableNannies.value;
    const term = searchTerm.value.toLowerCase();
    return availableNannies.value.filter((nanny) => nanny.name.toLowerCase().includes(term));
});
</script>

<template>
    <div class="min-h-[calc(100vh-80px)]">
        <div class="px-3 sm:px-6 py-6 sm:py-8">
            <!-- Header -->
            <header
                class="rounded-3xl p-4 sm:p-6 flex gap-3 flex-col sm:flex-row items-start sm:items-center justify-between backdrop-blur-xl border border-white/30 dark:border-white/10 shadow-lg bg-rose-50/30 dark:bg-rose-800/5"
            >
                <div>
                    <h1 class="text-xl sm:text-2xl font-semibold tracking-tight">Elegir niñera</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Cita: {{ formatDate(props.appointment.start_date) }} - {{ formatDate(props.appointment.end_date) }}
                    </p>
                </div>
                <Button variant="outline" size="sm" @click="router.get(route('bookings.show', props.booking.id))">
                    <Icon icon="lucide:arrow-left" class="mr-2 h-4 w-4" />
                    Volver
                </Button>
            </header>

            <!-- Top 3 Modal -->
            <Dialog :open="showTop3Modal" @update:open="closeTop3">
                <DialogContent class="sm:max-w-4xl">
                    <DialogHeader>
                        <DialogTitle class="text-2xl">Niñeras con mayor coincidencia</DialogTitle>
                        <DialogDescription> Estas son las niñeras más recomendadas para tu cita </DialogDescription>
                    </DialogHeader>

                    <div v-if="props.top3Nannies.length === 0" class="text-center py-8 text-muted-foreground">
                        <Icon icon="lucide:user-x" class="mx-auto h-12 w-12 mb-3 opacity-50" />
                        <p>No hay niñeras disponibles en este horario.</p>
                        <p class="text-sm mt-2">Intenta ajustar la hora o vuelve más tarde.</p>
                    </div>

                    <!-- Pyramid layout for top 3 -->
                    <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-4 py-4">
                        <!-- Side card (2nd place) -->
                        <div
                            v-if="props.top3Nannies[1]"
                            class="rounded-2xl border bg-card p-4 flex flex-col items-center text-center space-y-3 sm:mt-8"
                        >
                            <Avatar class="h-20 w-20">
                                <AvatarImage :src="props.top3Nannies[1].profile_photo_url || undefined" />
                                <AvatarFallback>{{ getInitials(props.top3Nannies[1].name) }}</AvatarFallback>
                            </Avatar>
                            <div>
                                <h3 class="font-semibold">{{ props.top3Nannies[1].name }}</h3>
                                <div class="flex flex-wrap gap-1 justify-center mt-2">
                                    <Badge v-for="q in props.top3Nannies[1].qualities.slice(0, 2)" :key="q" variant="secondary" class="text-[10px]">
                                        {{ props.qualities[q] || q }}
                                    </Badge>
                                </div>
                            </div>
                            <div class="flex gap-2 w-full">
                                <Button size="sm" variant="outline" class="flex-1" @click="openDetail(props.top3Nannies[1])"> Ver más </Button>
                                <Button size="sm" class="flex-1" @click="assignNanny(props.top3Nannies[1].id)"> Elegir </Button>
                            </div>
                        </div>

                        <!-- Center card (1st place - featured) -->
                        <div class="rounded-2xl border-2 border-primary bg-card p-4 flex flex-col items-center text-center space-y-3 relative">
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                                <Badge class="bg-primary">Destacada</Badge>
                            </div>
                            <Avatar class="h-24 w-24 ring-2 ring-primary ring-offset-2">
                                <AvatarImage :src="props.top3Nannies[0].profile_photo_url || undefined" />
                                <AvatarFallback>{{ getInitials(props.top3Nannies[0].name) }}</AvatarFallback>
                            </Avatar>
                            <div>
                                <h3 class="font-semibold text-lg">{{ props.top3Nannies[0].name }}</h3>
                                <div class="flex flex-wrap gap-1 justify-center mt-2">
                                    <Badge v-for="q in props.top3Nannies[0].qualities.slice(0, 3)" :key="q" class="text-[10px]">
                                        {{ props.qualities[q] || q }}
                                    </Badge>
                                </div>
                            </div>
                            <div class="flex gap-2 w-full">
                                <Button size="sm" variant="outline" class="flex-1" @click="openDetail(props.top3Nannies[0])"> Ver más </Button>
                                <Button size="sm" class="flex-1" @click="assignNanny(props.top3Nannies[0].id)"> Elegir </Button>
                            </div>
                        </div>

                        <!-- Side card (3rd place) -->
                        <div
                            v-if="props.top3Nannies[2]"
                            class="rounded-2xl border bg-card p-4 flex flex-col items-center text-center space-y-3 sm:mt-8"
                        >
                            <Avatar class="h-20 w-20">
                                <AvatarImage :src="props.top3Nannies[2].profile_photo_url || undefined" />
                                <AvatarFallback>{{ getInitials(props.top3Nannies[2].name) }}</AvatarFallback>
                            </Avatar>
                            <div>
                                <h3 class="font-semibold">{{ props.top3Nannies[2].name }}</h3>
                                <div class="flex flex-wrap gap-1 justify-center mt-2">
                                    <Badge v-for="q in props.top3Nannies[2].qualities.slice(0, 2)" :key="q" variant="secondary" class="text-[10px]">
                                        {{ props.qualities[q] || q }}
                                    </Badge>
                                </div>
                            </div>
                            <div class="flex gap-2 w-full">
                                <Button size="sm" variant="outline" class="flex-1" @click="openDetail(props.top3Nannies[2])"> Ver más </Button>
                                <Button size="sm" class="flex-1" @click="assignNanny(props.top3Nannies[2].id)"> Elegir </Button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <Button variant="ghost" @click="closeTop3">
                            <Icon icon="lucide:chevron-down" class="mr-2 h-4 w-4" />
                            Ver más opciones
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>

            <!-- Nanny Detail Modal -->
            <Dialog :open="showDetailModal" @update:open="closeDetail">
                <DialogContent class="sm:max-w-2xl">
                    <DialogHeader v-if="selectedNannyForDetail">
                        <div class="flex items-center gap-4 mb-4">
                            <Avatar class="h-20 w-20">
                                <AvatarImage :src="selectedNannyForDetail.profile_photo_url || undefined" />
                                <AvatarFallback>{{ getInitials(selectedNannyForDetail.name) }}</AvatarFallback>
                            </Avatar>
                            <div>
                                <DialogTitle class="text-2xl">{{ selectedNannyForDetail.name }}</DialogTitle>
                                <DialogDescription v-if="selectedNannyForDetail.experience">
                                    Experiencia desde {{ formatDate(selectedNannyForDetail.experience.start_date) }}
                                </DialogDescription>
                            </div>
                        </div>
                    </DialogHeader>

                    <div v-if="selectedNannyForDetail" class="space-y-4">
                        <!-- Description -->
                        <div v-if="selectedNannyForDetail.description">
                            <h4 class="font-semibold mb-2">Descripción</h4>
                            <p class="text-sm text-muted-foreground">{{ selectedNannyForDetail.description }}</p>
                        </div>

                        <!-- Qualities -->
                        <div v-if="selectedNannyForDetail.qualities.length > 0">
                            <h4 class="font-semibold mb-2">Cualidades</h4>
                            <div class="flex flex-wrap gap-2">
                                <Badge v-for="q in selectedNannyForDetail.qualities" :key="q">
                                    {{ props.qualities[q] || q }}
                                </Badge>
                            </div>
                        </div>

                        <!-- Careers -->
                        <div v-if="selectedNannyForDetail.careers.length > 0">
                            <h4 class="font-semibold mb-2">Carreras</h4>
                            <div class="flex flex-wrap gap-2">
                                <Badge v-for="c in selectedNannyForDetail.careers" :key="c" variant="secondary">
                                    <Icon icon="lucide:graduation-cap" class="mr-1 h-3 w-3" />
                                    {{ props.careers[c] || c }}
                                </Badge>
                            </div>
                        </div>

                        <!-- Courses -->
                        <div v-if="selectedNannyForDetail.courses.length > 0">
                            <h4 class="font-semibold mb-2">Cursos</h4>
                            <div class="flex flex-wrap gap-2">
                                <Badge v-for="c in selectedNannyForDetail.courses" :key="c" variant="outline">
                                    <Icon icon="lucide:book-open" class="mr-1 h-3 w-3" />
                                    {{ props.courseNames[c] || c }}
                                </Badge>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <Button variant="outline" class="flex-1" @click="closeDetail"> Cerrar </Button>
                            <Button class="flex-1" @click="assignNanny(selectedNannyForDetail.id)">
                                <Icon icon="lucide:user-check" class="mr-2 h-4 w-4" />
                                Elegir niñera
                            </Button>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>

            <!-- Main content - List of available nannies -->
            <section class="mt-6">
                <div class="rounded-3xl border border-white/30 bg-white/20 dark:border-white/10 dark:bg-white/5 backdrop-blur-xl shadow-lg p-4 sm:p-6">
                    <!-- Search bar -->
                    <div class="mb-4 flex items-center gap-4">
                        <div class="flex-1">
                            <Input v-model="searchTerm" placeholder="Buscar niñera por nombre..." class="max-w-md" />
                        </div>
                        <Button variant="outline" size="sm" @click="fetchNannies">
                            <Icon icon="lucide:refresh-cw" class="h-4 w-4" :class="{ 'animate-spin': isLoading }" />
                        </Button>
                    </div>

                    <!-- Loading state -->
                    <div v-if="isLoading" class="text-center py-12">
                        <Icon icon="lucide:loader-2" class="mx-auto h-8 w-8 animate-spin text-muted-foreground" />
                        <p class="mt-2 text-sm text-muted-foreground">Cargando niñeras disponibles...</p>
                    </div>

                    <!-- Empty state -->
                    <div v-else-if="filteredNannies.length === 0" class="text-center py-12 text-muted-foreground">
                        <Icon icon="lucide:user-x" class="mx-auto h-12 w-12 mb-3 opacity-50" />
                        <p class="font-medium">Sin niñeras disponibles en ese horario</p>
                        <p class="text-sm mt-2">Intenta ajustar la hora o vuelve más tarde</p>
                    </div>

                    <!-- Nanny cards grid -->
                    <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="nanny in filteredNannies"
                            :key="nanny.id"
                            class="rounded-2xl border border-white/20 bg-white/40 dark:bg-white/10 backdrop-blur-md p-4 space-y-3 shadow hover:shadow-md transition-shadow"
                        >
                            <div class="flex items-start gap-3">
                                <Avatar class="h-14 w-14 ring-1 ring-border">
                                    <AvatarImage :src="nanny.profile_photo_url || undefined" />
                                    <AvatarFallback>{{ getInitials(nanny.name) }}</AvatarFallback>
                                </Avatar>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-sm truncate">{{ nanny.name }}</h3>
                                    <p v-if="nanny.experience" class="text-xs text-muted-foreground">
                                        Desde {{ formatDate(nanny.experience.start_date) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Quick info -->
                            <div class="space-y-2">
                                <div v-if="nanny.qualities.length > 0" class="flex flex-wrap gap-1">
                                    <Badge v-for="q in nanny.qualities.slice(0, 3)" :key="q" class="text-[10px] px-1.5 py-0">
                                        {{ props.qualities[q] || q }}
                                    </Badge>
                                </div>
                                <div v-if="nanny.careers.length > 0" class="flex flex-wrap gap-1">
                                    <Badge v-for="c in nanny.careers.slice(0, 2)" :key="c" variant="secondary" class="text-[10px] px-1.5 py-0">
                                        <Icon icon="lucide:graduation-cap" class="mr-0.5 h-2.5 w-2.5" />
                                        {{ props.careers[c] || c }}
                                    </Badge>
                                </div>
                            </div>

                            <div class="flex gap-2 pt-2">
                                <Button size="sm" variant="outline" class="flex-1 h-8 text-xs" @click="openDetail(nanny)">
                                    <Icon icon="lucide:eye" class="mr-1 h-3 w-3" />
                                    Ver
                                </Button>
                                <Button size="sm" class="flex-1 h-8 text-xs" @click="assignNanny(nanny.id)">
                                    <Icon icon="lucide:user-check" class="mr-1 h-3 w-3" />
                                    Elegir
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
