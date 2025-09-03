<script setup lang="ts">
import type { Nanny } from '@/types/Nanny'
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Icon } from '@iconify/vue'

defineProps<{ nanny: Nanny }>()
</script>

<template>
  <Card class="bg-yellow-50 dark:bg-yellow-500/7 border-none shadow-sm">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <Icon icon="lucide:briefcase" /> Carreras
      </CardTitle>
    </CardHeader>
    <CardContent>
      <div v-if="nanny.careers?.length" class="flex flex-wrap gap-2">
        <Badge v-for="career in nanny.careers" :key="career.id" variant="secondary">
          {{ career.name }}
        </Badge>
      </div>
      <div v-else class="flex flex-col items-center text-muted-foreground">
        <Icon icon="lucide:ban" class="w-8 h-8 mb-2" />
        <span>Sin carreras</span>
      </div>
    </CardContent>
  </Card>
</template>
