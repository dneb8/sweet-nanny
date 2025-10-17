<script setup lang="ts">
import type { Nanny } from '@/types/Nanny'
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Icon } from '@iconify/vue'
import { ref, computed } from "vue"
import { Combobox, ComboboxAnchor, ComboboxInput, ComboboxList, ComboboxItem } from "@/components/ui/combobox"
import { QualityFormService } from "@/services/QualityFormService"
import { qualitiesList } from "@/enums/qualityenum"

const props = defineProps<{ nanny: Nanny }>()

// Instancia del servicio
const service = new QualityFormService(
  props.nanny.ulid ?? String(props.nanny.id),
  props.nanny.qualities?.map(q => q.name) ?? []
)

const query = ref("")

// Función para agregar cualidad y limpiar input
const addQuality = async (quality: string) => {
  await service.addQuality(quality)
  query.value = "" // limpia el input
}

// Filtrar cualidades por inicio de texto y ocultar ya asignadas
const filteredQualities = computed(() => {
  const text = query.value.toLowerCase().trim()
  return (text === "" ? qualitiesList : qualitiesList.filter(q => q.toLowerCase().startsWith(text)))
         .filter(q => !service.qualities.value.includes(q))
})

const open = ref(false)
</script>

<template>
  <Card class="bg-purple-50 dark:bg-purple-500/7 border-none shadow-sm">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <Icon icon="lucide:sparkles" /> Cualidades
      </CardTitle>
    </CardHeader>
    <CardContent>
      <!-- Etiquetas actuales -->
      <div v-if="service.qualities.value.length" class="flex flex-wrap gap-2 mb-4">
        <Badge
          v-for="quality in service.qualities.value"
          :key="quality"
          class="bg-purple-200 text-purple-900 flex items-center gap-1"
        >
          {{ quality }}
          <button
            @click.prevent="service.removeQuality(quality)"
            class="text-purple-700 hover:text-purple-900"
          >
            <Icon icon="lucide:x" class="w-3 h-3" />
          </button>
        </Badge>
      </div>
      <div v-else class="flex flex-col items-center text-muted-foreground mb-4">
        <Icon icon="lucide:square" class="w-8 h-8 mb-2" />
        <span>No definidas</span>
      </div>

      <!-- Barra de búsqueda con ícono -->
      <Combobox v-model:open="open" :ignore-filter="true">
        <ComboboxAnchor as-child class="relative w-full">
          <div class="relative w-full">
            <Icon
              icon="lucide:search"
              class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4"
            />
            <ComboboxInput
              v-model="query"
              placeholder="Agregar cualidad..."
              class="pl-10 w-full"
            />
          </div>
        </ComboboxAnchor>

        <ComboboxList v-if="open">
          <ComboboxItem
            v-for="quality in filteredQualities"
            :key="quality"
            :value="quality"
            @click.prevent="addQuality(quality)"
            class="cursor-pointer"
          >
            {{ quality }}
          </ComboboxItem>
        </ComboboxList>
      </Combobox>

      <!-- Estado de carga -->
      <div v-if="service.loading.value" class="mt-3 flex items-center gap-2 text-purple-500">
        <Icon icon="lucide:loader-2" class="animate-spin w-4 h-4" /> Guardando cambios...
      </div>
    </CardContent>
  </Card>
</template>
