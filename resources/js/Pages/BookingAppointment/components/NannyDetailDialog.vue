<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Nanny } from '@/types/Nanny'

const props = defineProps<{
  open: boolean
  nanny: Nanny | null
  qualities: Record<string, string>
  careers: Record<string, string>
  courseNames: Record<string, string>
}>()

const emit = defineEmits<{
  (e: 'update:open', v: boolean): void
  (e: 'close'): void
  (e: 'choose', n: Nanny): void
}>()

const hasNanny = computed(() => !!props.nanny)

// Calculate average rating
const averageRating = computed(() => {
  if (!props.nanny?.reviews || props.nanny.reviews.length === 0) return null
  const sum = props.nanny.reviews.reduce((acc, r) => acc + (r.rating || 0), 0)
  return (sum / props.nanny.reviews.length).toFixed(1)
})

const reviewCount = computed(() => props.nanny?.reviews?.length || 0)

function close() {
  emit('update:open', false)
  emit('close')
}
function initials(name: string) {
  return name.split(' ').map(s => s[0]).join('').toUpperCase().slice(0,2)
}
function formatDate(s?: string) {
  if (!s) return ''
  return new Date(s).toLocaleDateString('es-MX', { year:'numeric', month:'short', day:'numeric' })
}
</script>

<template>
  <Dialog :open="open" @update:open="(v: boolean) => emit('update:open', v)">
    <DialogContent class="sm:max-w-2xl">
      <DialogHeader v-if="hasNanny">
        <div class="flex items-center gap-4 mb-4">
          <Avatar class="h-20 w-20">
            <AvatarImage :src="nanny?.profile_photo_url || undefined" />
            <AvatarFallback>{{ nanny ? initials(nanny.name) : '—' }}</AvatarFallback>
          </Avatar>
          <div class="flex-1">
            <DialogTitle class="text-2xl">{{ nanny?.name }}</DialogTitle>
            <DialogDescription v-if="nanny?.experience" class="mt-1">
              Experiencia desde {{ formatDate(nanny?.experience?.start_date) }}
            </DialogDescription>
            <DialogDescription v-if="averageRating" class="flex items-center gap-2 mt-1">
              <Icon icon="lucide:star" class="h-4 w-4 text-yellow-500" />
              <span class="font-semibold">{{ averageRating }}</span>
              <span class="text-muted-foreground">({{ reviewCount }} {{ reviewCount === 1 ? 'reseña' : 'reseñas' }})</span>
            </DialogDescription>
          </div>
        </div>
      </DialogHeader>

      <div v-if="hasNanny" class="space-y-4">
        <div v-if="nanny?.description">
          <h4 class="font-semibold mb-2">Descripción</h4>
          <p class="text-sm text-muted-foreground">{{ nanny?.description }}</p>
        </div>

        <div v-if="(nanny?.qualities?.length ?? 0) > 0">
          <h4 class="font-semibold mb-2">Cualidades</h4>
          <div class="flex flex-wrap gap-2">
            <Badge v-for="q in nanny!.qualities" :key="q" class="bg-purple-200 text-purple-900 dark:text-purple-200 dark:bg-purple-900/60 dark:border-purple-200">
              {{ qualities[q] || q }}
            </Badge>
          </div>
        </div>

        <div v-if="(nanny?.careers?.length ?? 0) > 0">
          <h4 class="font-semibold mb-2">Carreras</h4>
          <div class="flex flex-wrap gap-2">
            <Badge v-for="c in nanny!.careers" :key="c" class="bg-indigo-200 text-indigo-900 dark:text-indigo-100 dark:bg-indigo-500/40 dark:border-indigo-200">
              <Icon icon="lucide:graduation-cap" class="mr-1 h-3 w-3" />
              {{ careers[c] || c }}
            </Badge>
          </div>
        </div>

        <div v-if="(nanny?.courses?.length ?? 0) > 0">
          <h4 class="font-semibold mb-2">Cursos</h4>
          <div class="flex flex-wrap gap-2">
            <Badge v-for="c in nanny!.courses" :key="c" class="bg-emerald-200 text-emerald-900 dark:text-emerald-100 dark:bg-emerald-900/60 dark:border-emerald-200">
              <Icon icon="lucide:book-open" class="mr-1 h-3 w-3" />
              {{ courseNames[c] || c }}
            </Badge>
          </div>
        </div>

        <div class="flex gap-3 pt-4">
          <Button variant="outline" class="flex-1" @click="close">Cerrar</Button>
          <Button class="flex-1" @click="nanny && emit('choose', nanny)">
            <Icon icon="lucide:user-check" class="mr-2 h-4 w-4" />
            Elegir niñera
          </Button>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>
