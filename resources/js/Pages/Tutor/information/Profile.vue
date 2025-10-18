<script setup lang="ts">
import type { Tutor } from '@/types/Tutor';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Icon } from '@iconify/vue';

const props = defineProps<{ tutor: Tutor }>();
</script>

<template>
    <Card
        class="p-6 flex flex-col md:flex-row items-center gap-6 bg-gradient-to-r from-blue-50 dark:from-blue-700/5 to-cyan-50 dark:to-cyan-700/5 border-none shadow-md"
    >
        <div class="relative">
            <img
                :src="props.tutor.user?.avatar_url ?? 'https://randomuser.me/api/portraits/men/32.jpg'"
                alt="Foto de perfil"
                class="w-28 h-28 rounded-full border-4 border-white shadow-lg object-cover"
            />
            <div class="absolute -bottom-2 -right-2 bg-primary text-primary-foreground rounded-full p-2 shadow-lg">
                <Icon icon="mdi:school-outline" class="w-5 h-5" />
            </div>
        </div>
        <div class="flex-1 space-y-3 text-center md:text-left">
            <div>
                <h1 class="text-2xl font-bold">
                    {{ props.tutor.user?.name ?? 'Tutor sin nombre' }}
                    {{ props.tutor.user?.surnames ?? '' }}
                </h1>
                <p class="text-sm text-muted-foreground mt-1">
                    {{ props.tutor.user?.email ?? 'Sin email' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                <Badge variant="secondary" class="px-3 py-1">
                    <Icon icon="mdi:account-tie" class="w-4 h-4 mr-1" />
                    Tutor
                </Badge>
                <Badge v-if="props.tutor.user?.email_verified_at" variant="outline" class="px-3 py-1 border-emerald-500 text-emerald-700">
                    <Icon icon="mdi:check-decagram" class="w-4 h-4 mr-1" />
                    Verificado
                </Badge>
            </div>
            <div v-if="props.tutor.emergency_contact || props.tutor.emergency_number" class="flex items-center gap-4 pt-2">
                <div v-if="props.tutor.emergency_contact" class="flex items-center gap-2 text-sm">
                    <Icon icon="mdi:phone-alert-outline" class="w-4 h-4 text-muted-foreground" />
                    <span class="text-muted-foreground">Contacto emergencia:</span>
                    <span class="font-medium">{{ props.tutor.emergency_contact }}</span>
                </div>
                <div v-if="props.tutor.emergency_number" class="flex items-center gap-2 text-sm">
                    <Icon icon="mdi:cellphone" class="w-4 h-4 text-muted-foreground" />
                    <span class="font-medium">{{ props.tutor.emergency_number }}</span>
                </div>
            </div>
        </div>
    </Card>
</template>
