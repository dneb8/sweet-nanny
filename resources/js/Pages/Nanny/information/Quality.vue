<script setup lang="ts">
import type { Nanny } from '@/types/Nanny'
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Icon } from '@iconify/vue'
import { ref, computed } from "vue"
import {
  Popover, PopoverTrigger, PopoverContent
} from '@/components/ui/popover'
import {
  Command, CommandInput, CommandEmpty, CommandList, CommandGroup, CommandItem
} from '@/components/ui/command'
import { Check, ChevronsUpDown } from 'lucide-vue-next'
import { cn } from '@/utils'
import { QualityFormService } from "@/services/QualityFormService"
import { qualitiesList } from "@/enums/qualityenum"

const props = defineProps<{ nanny: Nanny }>()

// Servicio
const service = new QualityFormService(
  props.nanny.ulid ?? String(props.nanny.id),
  props.nanny.qualities?.map(q => q.name) ?? []
)

// Popover / búsqueda
const open = ref(false)
const query = ref('')

// Lista filtrada (si no escribes, muestra todo; oculta las ya asignadas)
const filtered = computed(() => {
  const text = query.value.toLowerCase().trim()
  const base = text ? qualitiesList.filter(q => q.toLowerCase().includes(text)) : qualitiesList
  return base.filter(q => !service.qualities.value.includes(q))
})

const addQuality = async (quality: string) => {
  await service.addQuality(quality)
  // Mantener abierto para agregar varias y limpiar búsqueda
  query.value = ''
  open.value = true
}

const removeQuality = async (quality: string) => {
  await service.removeQuality(quality)
}
</script>

<template>
  <Card class="bg-purple-50 dark:bg-purple-500/7 border-none shadow-sm">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <Icon icon="lucide:sparkles" /> Cualidades
      </CardTitle>
    </CardHeader>

    <!-- Contenedor relativo para overlay del loader -->
    <CardContent class="relative">
      <!-- Loader overlay NO intrusivo -->
      <div
        v-show="service.loading.value"
        class="pointer-events-none absolute right-2 top-2 flex items-center gap-2 text-purple-500 text-xs"
      >
        <Icon icon="lucide:loader-2" class="animate-spin w-4 h-4" />
        Guardando cambios...
      </div>

      <!-- Etiquetas actuales -->
      <div v-if="service.qualities.value.length" class="flex flex-wrap gap-2 mb-4">
        <Badge
          v-for="quality in service.qualities.value"
          :key="quality"
          class="bg-purple-200 text-purple-900 dark:text-purple-200 dark:bg-purple-900/60  dark:border-purple-200 flex items-center gap-1"
        >
          {{ quality }}
          <button
            @click.prevent="removeQuality(quality)"
            :class="[
              'text-purple-700 hover:text-purple-900',
              'dark:text-purple-200 dark:hover:text-purple-400'
            ]"
            :disabled="service.loading.value"
            title="Quitar"
          >
            <Icon icon="lucide:x" class="w-3 h-3" />
          </button>
        </Badge>
      </div>
      <div v-else class="flex flex-col items-center text-muted-foreground mb-4">
        <Icon icon="lucide:square" class="w-8 h-8 mb-2" />
        <span>No definidas</span>
      </div>

      <!-- Combobox estilo Command -->
      <div class="w-full max-w-xl">
        <Popover v-model:open="open">
          <PopoverTrigger as-child>
            <Button
              variant="outline"
              role="combobox"
              :aria-expanded="open"
              class="w-full justify-between"
              :disabled="service.loading.value"
              @click="open = true"
            >
              <span class="truncate text-muted-foreground">
                Agregar cualidad...
              </span>
              <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
            </Button>
          </PopoverTrigger>

          <!-- Igualar ancho al trigger -->
          <PopoverContent class="w-[--reka-popper-anchor-width] p-0">
            <Command>
              <CommandInput
                v-model="query"
                class="h-9"
                :placeholder="service.loading.value ? 'Cargando...' : 'Buscar cualidad...'"
                :disabled="service.loading.value"
              />
              <CommandEmpty>
                No se encontraron cualidades.
              </CommandEmpty>

              <CommandList class="max-h-64 overflow-auto">
                <CommandGroup>
                  <CommandItem
                    v-for="q in filtered"
                    :key="q"
                    :value="q"
                    @select="(ev: CustomEvent<{ value: string }>) => {
                      const val = String(ev.detail.value || q)
                      addQuality(val)
                    }"
                  >
                    {{ q }}
                    <Check
                      :class="cn('ml-auto h-4 w-4', 'opacity-0')"
                    />
                  </CommandItem>
                </CommandGroup>
              </CommandList>
            </Command>
          </PopoverContent>
        </Popover>
      </div>
    </CardContent>
  </Card>
</template>
