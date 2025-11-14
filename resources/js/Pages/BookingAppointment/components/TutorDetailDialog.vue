<script setup lang="ts">
import { computed } from 'vue';
import { Icon } from '@iconify/vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Tutor } from '@/types/Tutor';

const props = defineProps<{
    open: boolean;
    tutor: Tutor | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', v: boolean): void;
    (e: 'close'): void;
}>();

const hasTutor = computed(() => !!props.tutor);

// Calculate average rating
const averageRating = computed(() => {
    if (!props.tutor?.reviews || props.tutor.reviews.length === 0) return null;
    const sum = props.tutor.reviews.reduce((acc, r) => acc + (r.rating || 0), 0);
    return (sum / props.tutor.reviews.length).toFixed(1);
});

const reviewCount = computed(() => props.tutor?.reviews?.length || 0);

function close() {
    emit('update:open', false);
    emit('close');
}

function initials(name: string) {
    return name
        .split(' ')
        .map((s) => s[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
}
</script>

<template>
    <Dialog :open="open" @update:open="(v: boolean) => emit('update:open', v)">
        <DialogContent class="sm:max-w-2xl">
            <DialogHeader v-if="hasTutor">
                <div class="flex items-center gap-4 mb-4">
                    <Avatar class="h-20 w-20">
                        <AvatarImage :src="tutor?.user?.avatar_url || undefined" />
                        <AvatarFallback>{{ tutor?.user ? initials(tutor.user.name || 'T') : '—' }}</AvatarFallback>
                    </Avatar>
                    <div class="flex-1">
                        <DialogTitle class="text-2xl">{{ tutor?.user?.name }} {{ tutor?.user?.surnames }}</DialogTitle>
                        <DialogDescription v-if="averageRating" class="flex items-center gap-2 mt-1">
                            <Icon icon="lucide:star" class="h-4 w-4 text-yellow-500" />
                            <span class="font-semibold">{{ averageRating }}</span>
                            <span class="text-muted-foreground">({{ reviewCount }} {{ reviewCount === 1 ? 'reseña' : 'reseñas' }})</span>
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <div v-if="hasTutor" class="space-y-4">
                <!-- Emergency Contact Info -->
                <div v-if="tutor?.emergency_contact || tutor?.emergency_number" class="rounded-lg border p-4 bg-muted/30">
                    <h4 class="font-semibold mb-3 flex items-center gap-2">
                        <Icon icon="lucide:phone" class="h-4 w-4 text-primary" />
                        Contacto de Emergencia
                    </h4>

                    <div class="space-y-2">
                        <div v-if="tutor?.emergency_contact" class="flex items-center gap-2 text-sm">
                            <Icon icon="lucide:user" class="h-4 w-4 text-muted-foreground" />
                            <span>{{ tutor.emergency_contact }}</span>
                        </div>

                        <div v-if="tutor?.emergency_number" class="flex items-center gap-2 text-sm">
                            <Icon icon="lucide:phone" class="h-4 w-4 text-muted-foreground" />
                            <a :href="`tel:${tutor.emergency_number}`" class="text-primary hover:underline">
                                {{ tutor.emergency_number }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Children Info -->
                <div v-if="tutor?.children && tutor.children.length > 0" class="rounded-lg border p-4">
                    <h4 class="font-semibold mb-3 flex items-center gap-2">
                        <Icon icon="lucide:baby" class="h-4 w-4 text-primary" />
                        Niños ({{ tutor.children.length }})
                    </h4>
                    <div class="flex flex-wrap gap-2">
                        <Badge
                            v-for="child in tutor.children"
                            :key="child.id"
                            class="bg-purple-200 text-purple-900 dark:text-purple-200 dark:bg-purple-900/60 dark:border-purple-200"
                        >
                            {{ child.name }}
                        </Badge>
                    </div>
                </div>

                <!-- Addresses -->
                <div v-if="tutor?.addresses && tutor.addresses.length > 0" class="rounded-lg border p-4">
                    <h4 class="font-semibold mb-3 flex items-center gap-2">
                        <Icon icon="lucide:map-pin" class="h-4 w-4 text-primary" />
                        Direcciones
                    </h4>
                    <div class="space-y-2">
                        <div v-for="address in tutor.addresses" :key="address.id" class="text-sm text-muted-foreground">
                            {{ address.street }} {{ address.external_number }}, {{ address.neighborhood }}
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <Button variant="outline" class="flex-1" @click="close">Cerrar</Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
