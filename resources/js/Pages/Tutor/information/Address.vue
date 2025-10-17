<script setup lang="ts">
import type { Tutor } from '@/types/Tutor';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Icon } from '@iconify/vue';

const props = defineProps<{ tutor: Tutor }>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle class="flex items-center gap-2">
                <Icon icon="mdi:map-marker-outline" class="w-5 h-5 text-primary" />
                Dirección
            </CardTitle>
        </CardHeader>
        <CardContent>
            <div v-if="props.tutor.address" class="space-y-2">
                <div class="flex items-start gap-2">
                    <Icon icon="mdi:home-outline" class="w-4 h-4 mt-0.5 text-muted-foreground" />
                    <div>
                        <p class="text-sm font-medium">{{ props.tutor.address.street }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ props.tutor.address.city }}, {{ props.tutor.address.state }}
                        </p>
                        <p class="text-sm text-muted-foreground">{{ props.tutor.address.postal_code }}</p>
                    </div>
                </div>
            </div>
            <div v-else class="flex items-center justify-center py-8 text-muted-foreground">
                <div class="text-center">
                    <Icon icon="mdi:map-marker-off-outline" class="w-12 h-12 mx-auto mb-2 opacity-30" />
                    <p class="text-sm">No se ha registrado dirección</p>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
