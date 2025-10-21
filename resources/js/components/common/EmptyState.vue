<script setup lang="ts">
import { Icon } from "@iconify/vue"
import { Button } from "@/components/ui/button"

const props = withDefaults(defineProps<{
  icon?: string
  title?: string
  description?: string
  actionLabel?: string
  imageSrc?: string
  imageAlt?: string
}>(), {
  icon: "solar:inbox-line-broken",
  title: "No hay datos",
  description: "No se encontraron elementos para mostrar",
})

const emit = defineEmits<{
  (e: 'action'): void
}>()

function onActionClick() {
  emit('action')
}
</script>

<template>
  <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
    <!-- Image or Icon -->
    <div class="mb-4">
      <img 
        v-if="props.imageSrc" 
        :src="props.imageSrc" 
        :alt="props.imageAlt || props.title" 
        class="w-32 h-32 object-contain opacity-60"
      />
      <Icon 
        v-else 
        :icon="props.icon" 
        class="w-16 h-16 text-muted-foreground/50"
      />
    </div>

    <!-- Title -->
    <h3 class="text-lg font-semibold text-foreground mb-2">
      {{ props.title }}
    </h3>

    <!-- Description -->
    <p class="text-sm text-muted-foreground max-w-md mb-6">
      {{ props.description }}
    </p>

    <!-- Action Button (optional) -->
    <slot name="action">
      <Button 
        v-if="props.actionLabel" 
        @click="onActionClick" 
        size="sm"
        variant="outline"
      >
        {{ props.actionLabel }}
      </Button>
    </slot>

    <!-- Additional custom content slot -->
    <div v-if="$slots.default" class="mt-4">
      <slot />
    </div>
  </div>
</template>
