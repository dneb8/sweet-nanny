<script setup lang="ts">
import type { Nanny } from '@/types/Nanny'
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Icon } from '@iconify/vue'

defineProps<{
  nanny: Nanny,
  isOwner: boolean
}>()
</script>

<template>
  <Card class="bg-blue-50 dark:bg-blue-500/5 border-none shadow-sm">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <Icon icon="lucide:map-pin" /> Dirección
      </CardTitle>
    </CardHeader>
    <CardContent>
      <p v-if="nanny.address">
        {{ nanny.address.street }}, {{ nanny.address.city }}
      </p>
      <div v-else class="flex flex-col items-center text-muted-foreground">
        <Icon icon="lucide:map" class="w-8 h-8 mb-2" />
        <span>No registrada</span>
        <Button v-if="isOwner" variant="outline" size="sm" class="mt-2">
          <Icon icon="lucide:plus" class="mr-1" /> Agregar dirección
        </Button>
      </div>
    </CardContent>
  </Card>
</template>
