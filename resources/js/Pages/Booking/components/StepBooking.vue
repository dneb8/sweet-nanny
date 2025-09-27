<script setup lang="ts">
import { computed } from "vue"
import { Textarea } from "@/components/ui/textarea"
import { Label } from "@/components/ui/label"
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip"
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Icon } from "@iconify/vue"
import { useBoundField } from "@/services/bookingFormService"

const desc = useBoundField<string>("booking.description")
const recurrent = useBoundField<boolean>("booking.recurrent")

// Mapear Tabs <-> boolean
const tabValue = computed({
  get: () => (recurrent.value ? "recurrente" : "fijo"),
  set: (val: string) => (recurrent.value = val === "recurrente"),
})
</script>

<template>
  <div class="space-y-8">
    <!-- Descripción -->
    <div class="space-y-2">
      <div class="flex items-center gap-2">
        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger as-child>
              <span class="relative inline-flex cursor-help items-center justify-center" aria-label="Ayuda: descripción">
                <Icon icon="ph:question-bold" class="relative z-10 h-4 w-4 text-gray-500 dark:text-gray-100" />
                <span class="pointer-events-none absolute left-1/2 -translate-x-1/2 bottom-[-2px] h-1.5 w-6 rounded-full animate-pulse bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-400 opacity-80 blur-[3px]" />
              </span>
            </TooltipTrigger>
            <TooltipContent class="max-w-xs text-xs leading-relaxed">
              <p class="font-semibold mb-1">¿Qué escribir aquí?</p>
              <p>Describe brevemente el servicio (qué, para quién y horario).</p>
              <ul class="mt-2 list-disc pl-4">
                <li>Ej: “Cuidado infantil lun–vie, 3:00–6:00 pm”.</li>
                <li>Evita datos personales sensibles.</li>
                <li>Sé claro y específico.</li>
              </ul>
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
        <Label for="description" class="text-sm font-medium">Descripción</Label>
      </div>

      <Textarea id="description" v-model="desc.value" class="mt-1 resize-none" placeholder="Escribir descripción..." />
      <p v-if="desc.errorMessage" class="text-xs text-red-500 mt-1">{{ desc.errorMessage }}</p>
    </div>

    <!-- Fijo / Recurrente (Tabs) -->
    <div class="space-y-2">
      <div class="flex items-center gap-2">
        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger as-child>
              <span class="relative inline-flex cursor-help items-center justify-center" aria-label="Ayuda: recurrencia">
                <Icon icon="ph:question-bold" class="relative z-10 h-4 w-4 text-gray-500 dark:text-gray-100" />
                <span class="pointer-events-none absolute left-1/2 -translate-x-1/2 bottom-[-2px] h-1.5 w-6 rounded-full animate-pulse bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-400 opacity-80 blur-[3px]" />
              </span>
            </TooltipTrigger>
            <TooltipContent class="max-w-xs text-xs leading-relaxed">
              <p class="font-semibold mb-1">Tipo de servicio</p>
              <ul class="mt-1 list-disc pl-4">
                <li><strong>Fijo:</strong> una sola cita.</li>
                <li><strong>Recurrente:</strong> varias citas (ej. lun–vie, 3–6 pm).</li>
              </ul>
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
        <Label class="text-sm font-medium">Tipo</Label>
      </div>

      <Tabs v-model="tabValue" class="w-full">
        <TabsList class="grid w-full grid-cols-2">
          <TabsTrigger value="fijo">Fijo</TabsTrigger>
          <TabsTrigger value="recurrente">Recurrente</TabsTrigger>
        </TabsList>
      </Tabs>
      <!-- Hint opcional -->
      <p class="text-xs text-muted-foreground">
        Selecciona <strong>Recurrente</strong> si quieres programar varias citas automáticamente.
      </p>
    </div>
  </div>
</template>
