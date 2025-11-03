<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Nanny } from '@/types/Nanny'

defineProps<{
  nanny: Nanny
  qualities: Record<string, string>
  careers: Record<string, string>
}>()

const emit = defineEmits<{
  (e: 'see', n: Nanny): void
  (e: 'choose', n: Nanny): void
}>()

function getInitials(name: string) {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0,2)
}
function formatDate(s?: string) {
  if (!s) return ''
  return new Date(s).toLocaleDateString('es-MX', { year:'numeric', month:'short', day:'numeric' })
}
</script>

<template>
  <div
    class="rounded-2xl border border-white/20 bg-white/40 dark:bg-white/10 backdrop-blur-md p-4 space-y-3 shadow hover:shadow-md transition-shadow"
  >
    <div class="flex items-start gap-3">
      <Avatar class="h-14 w-14 ring-1 ring-border">
        <AvatarImage :src="nanny.profile_photo_url || undefined" />
        <AvatarFallback>{{ getInitials(nanny.name) }}</AvatarFallback>
      </Avatar>
      <div class="flex-1 min-w-0">
        <h3 class="font-semibold text-sm truncate">{{ nanny.name }}</h3>
        <p v-if="nanny.experience" class="text-xs text-muted-foreground">
          Desde {{ formatDate(nanny.experience?.start_date) }}
        </p>
      </div>
    </div>

    <div class="space-y-2">
      <div v-if="nanny.qualities.length > 0" class="flex flex-wrap gap-1">
        <Badge v-for="q in nanny.qualities.slice(0, 3)" :key="q" class="text-[10px] px-1.5 py-0 bg-purple-200 text-purple-900 dark:text-purple-200 dark:bg-purple-900/60 dark:border-purple-200">
          {{ qualities[q] || q }}
        </Badge>
      </div>
      <div v-if="nanny.careers.length > 0" class="flex flex-wrap gap-1">
        <Badge v-for="c in nanny.careers.slice(0, 2)" :key="c" class="text-[10px] px-1.5 py-0 bg-indigo-200 text-indigo-900 dark:text-indigo-100 dark:bg-indigo-500/40 dark:border-indigo-200">
          <Icon icon="lucide:graduation-cap" class="mr-0.5 h-2.5 w-2.5" />
          {{ careers[c] || c }}
        </Badge>
      </div>
    </div>

    <div class="flex gap-2 pt-2">
      <Button size="sm" variant="outline" class="flex-1 h-8 text-xs" @click="emit('see', nanny)">
        <Icon icon="lucide:eye" class="mr-1 h-3 w-3" />
        Ver
      </Button>
      <Button size="sm" class="flex-1 h-8 text-xs" @click="emit('choose', nanny)">
        <Icon icon="lucide:user-check" class="mr-1 h-3 w-3" />
        Elegir
      </Button>
    </div>
  </div>
</template>
