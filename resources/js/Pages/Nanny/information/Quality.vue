<script setup lang="ts">
import type { Nanny } from '@/types/Nanny'
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Icon } from '@iconify/vue'

defineProps<{ nanny: Nanny }>()
</script>

<template>
  <Card class="bg-purple-50 dark:bg-purple-500/7 border-none shadow-sm">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <Icon icon="lucide:sparkles" /> Cualidades
      </CardTitle>
    </CardHeader>
    <CardContent>
      <div v-if="nanny.qualities?.length" class="flex flex-wrap gap-2">
        <Badge v-for="quality in nanny.qualities" :key="quality.id" class="bg-purple-200 text-purple-900">
          {{ quality.name }}
        </Badge>
      </div>
      <div v-else class="flex flex-col items-center text-muted-foreground">
        <Icon icon="lucide:square" class="w-8 h-8 mb-2" />
        <span>No definidas</span>
      </div>
    </CardContent>
  </Card>
</template>
