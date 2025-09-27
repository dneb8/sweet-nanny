<script setup lang="ts">
import { Textarea } from "@/components/ui/textarea"
import { Label } from "@/components/ui/label"
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip"
import { useBoundField } from "@/services/bookingFormService"

const desc = useBoundField<string>("booking.description")

</script>

<template>
  <div class="space-y-6">
    <!-- Descripción -->
    <div class="space-y-2">
      <div class="flex items-center gap-2">
        <TooltipProvider><Tooltip>
          <TooltipTrigger as-child>
            <span class="relative inline-flex cursor-help items-center justify-center">
              <Icon icon="ph:question-bold" class="relative z-10 size-4 text-gray-500 dark:text-gray-100" />
              <span class="pointer-events-none absolute -translate-x-1/5 bottom-[-2px] size-4 rounded-full animate-pulse bg-gradient-to-r from-rose-200 via-rose-300 to-rose-400 opacity-70 blur-[3px]" />
            </span>
          </TooltipTrigger>
          <TooltipContent class="max-w-xs text-xs leading-relaxed">
            <p class="font-semibold mb-1">¿Qué escribir?</p><p>Comentarios y datos importantes.</p>
          </TooltipContent>
        </Tooltip></TooltipProvider>
        <Label for="description" class="text-sm font-medium">Descripción</Label>
      </div>
      <Textarea id="description" v-model="desc.value.value" class="mt-1 resize-none" placeholder="Escribir descripción..." />
      <p v-if="desc.errorMessage" class="text-xs text-red-500 mt-1">{{ desc.errorMessage }}</p>
    </div>


  </div>
</template>
