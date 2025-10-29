<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { Button } from '@/components/ui/button';

const props = withDefaults(
    defineProps<{
        icon?: string;
        image?: string;
        title?: string;
        description?: string;
        ctaText?: string;
        ctaAction?: () => void;
    }>(),
    {
        icon: 'mdi:inbox-outline',
        title: 'No hay datos',
        description: 'No se encontraron elementos para mostrar.',
    }
);

const emit = defineEmits<{
    cta: [];
}>();

function handleCta() {
    if (props.ctaAction) {
        props.ctaAction();
    } else {
        emit('cta');
    }
}
</script>

<template>
    <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
        <!-- Image or Icon -->
        <div class="mb-4">
            <img v-if="image" :src="image" alt="Empty state" class="w-32 h-32 object-contain opacity-50" />
            <div v-else class="text-muted-foreground/40">
                <Icon :icon="icon" class="w-20 h-20" />
            </div>
        </div>

        <!-- Title -->
        <h3 class="text-lg font-semibold text-foreground mb-2">
            {{ title }}
        </h3>

        <!-- Description -->
        <p class="text-sm text-muted-foreground max-w-sm mb-6">
            {{ description }}
        </p>

        <!-- CTA Button (optional) -->
        <Button v-if="ctaText" @click="handleCta" variant="outline">
            {{ ctaText }}
        </Button>

        <!-- Custom slot for additional content -->
        <slot />
    </div>
</template>
