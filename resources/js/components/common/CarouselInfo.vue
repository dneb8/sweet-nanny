<script setup lang="ts">
import { ref, computed, watch } from 'vue' 
// Asegúrate de que este import sea correcto para tu entorno
import type { CarouselApi } from '@/components/ui/carousel' 

import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselNext,
  CarouselPrevious,
} from '@/components/ui/carousel'

const props = withDefaults(defineProps<{
  items: any[]
  title?: string
  getKey: (item: any) => string | number
  showCounter?: boolean
}>(), {
  showCounter: true
})

// Estado para almacenar la API de Embla. Inicializada como null.
const emblaApi = ref<CarouselApi | null>(null) 
const currentIndex = ref(0) 

const hasMany = computed(() => props.items.length > 1)

// Función segura para actualizar el índice usando la API
const onSelect = () => {
  // Verificamos si emblaApi existe
  if (!emblaApi.value) return 
  currentIndex.value = emblaApi.value.selectedScrollSnap()
}

// Usamos watch para inicializar listeners una vez que la API se cargue
watch(emblaApi, (api) => {
  if (api) {
    // Inicializar el contador
    onSelect() 
    
    // Agregar listener para actualizar el contador
    api.on('select', onSelect)
    api.on('reInit', onSelect) 
  }
})
</script>

<template>
  <div class="w-full">
    <div v-if="title" class="mb-2 font-semibold text-lg">{{ title }}</div>

    <Carousel
      class="w-full relative"
      :opts="{ align: 'start', loop: true }"
      @init-api="(api: CarouselApi) => emblaApi = api as CarouselApi"
    >
      <CarouselContent>
        <CarouselItem
          v-for="(item, idx) in items.length ? items : [{}]"
          :key="items.length ? getKey(item) : 'empty'"
          class="w-full p-2"
        >
          <slot v-if="items.length" name="item" :item="item" :index="idx" />
          <slot v-else name="empty" />
        </CarouselItem>
      </CarouselContent>

      <CarouselPrevious 
        v-if="hasMany" 
        class="absolute left-[-1rem] top-1/2 -translate-y-1/2" 
      />
      <CarouselNext 
        v-if="hasMany" 
        class="absolute right-[-1rem] top-1/2 -translate-y-1/2" 
      />
    </Carousel>

    <div v-if="showCounter && hasMany" class="text-center mt-2 text-sm text-muted-foreground">
      {{ currentIndex + 1 }} / {{ items.length }}
    </div>
  </div>
</template>