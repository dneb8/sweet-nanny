<script setup lang="ts">
import type { Nanny } from '@/types/Nanny'
import { Card } from "@/components/ui/card"
import { Progress } from "@/components/ui/progress"
import { Badge } from "@/components/ui/badge"

const props = defineProps<{ nanny: Nanny }>()

// Calcular % de completitud
const sections = [
  { key: 'bio', value: props.nanny.bio },
  { key: 'address', value: props.nanny.addresses },
  { key: 'courses', value: props.nanny.courses },
  { key: 'careers', value: props.nanny.careers },
  { key: 'qualities', value: props.nanny.qualities },
]

const completedSections = sections.filter(
  s => s.value && (Array.isArray(s.value) ? s.value.length > 0 : true)
).length
const completion = Math.round((completedSections / sections.length) * 100)
</script>

<template>
  <Card class="p-6 flex flex-col md:flex-row items-center gap-6 bg-gradient-to-r from-pink-50 dark:from-pink-700/5 to-purple-50 dark:to-purple-700/5 border-none shadow-md">
    <img 
      src="https://randomuser.me/api/portraits/women/68.jpg" 
      alt="Foto de perfil" 
      class="w-28 h-28 rounded-sm border-4 border-white shadow-md object-cover"
    />
    <div class="flex-1 space-y-2 text-center md:text-left">
        <h1 class="text-2xl font-bold">
            {{ props.nanny.user?.name ?? 'Nanny sin nombre' }}
            {{ props.nanny.user?.surnames ?? '' }}
        </h1>
        <p class="text-muted-foreground">
            <span v-if="props.nanny.bio">{{ props.nanny.bio }}</span>
            <span v-else class="italic">Ninguna descripción aún</span>
        </p>
      <div class="flex items-center justify-center md:justify-start gap-3">
        <Badge v-if="completion !== 100" variant="secondary" class="px-3 py-1 text-sm">
          Perfil {{ completion }}% completado
        </Badge>
      </div>
      <Progress v-if="completion !== 100" v-model="completion" class="h-2 mt-2" />
    </div>
  </Card>
</template>
