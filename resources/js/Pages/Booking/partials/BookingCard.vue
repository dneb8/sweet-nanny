<script setup lang="ts">
import { Card, CardHeader, CardContent } from '@/components/ui/card'
import { ScrollArea, ScrollBar } from "@/components/ui/scroll-area"
import { Icon } from '@iconify/vue'
import type { Booking } from '@/types/Booking'
import { getRoleLabelByString, RoleEnum } from '@/enums/role.enum'

const props = defineProps<{ booking: Booking }>()

function getRoleBadgeClass(role?: string) {
  switch (role) {
    case RoleEnum.ADMIN: return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300'
    case RoleEnum.NANNY: return 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300'
    case RoleEnum.TUTOR: return 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-300'
    default: return 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300'
  }
}
</script>

<template>
  <Card class="relative overflow-hidden">
    <!-- Cabecera (click sin acciones) -->
    <CardHeader class="flex flex-row gap-4 items-start px-4">
      <!-- Avatar -->
      <div class="flex-none w-20 flex flex-col items-center">
        <div class="w-16 h-16 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center border overflow-hidden">
          <Icon icon="mdi:image-outline" class="w-8 h-8 text-slate-400" />
        </div>
      </div>

      <!-- Info usuario -->
      <div class="flex-1 min-w-0">
        <span
          :class="[
            'inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium',
          ]"
        >
        </span>

        <div class="mt-2 flex items-center gap-2 min-w-0">
          <h3 class="text-sm font-semibold truncate">
          </h3>
          <Icon
            icon="mdi:check-circle"
            class="w-4 h-4 text-emerald-500"
          />
        </div>

        <p class="mt-1 text-xs text-muted-foreground truncate">
        </p>
      </div>
    </CardHeader>

    <!-- Habilidades (solo nanny) -->
    <CardContent>
      <ScrollArea class="w-full whitespace-nowrap">
        <div class="flex gap-2">
          <!-- Example static quality, replace with v-for as needed -->
          <span
            class="flex-none text-xs px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-800"
          >
            Habilidad
          </span>
        </div>
        <ScrollBar orientation="horizontal" class="pt-1 overflow-auto" />
      </ScrollArea>
    </CardContent>
  </Card>
</template>
