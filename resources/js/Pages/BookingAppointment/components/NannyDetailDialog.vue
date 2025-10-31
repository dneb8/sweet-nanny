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
          <div>
            <DialogTitle class="text-2xl">{{ nanny?.name }}</DialogTitle>
            <DialogDescription v-if="nanny?.experience">
              Experiencia desde {{ formatDate(nanny?.experience?.start_date) }}
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
            <Badge v-for="q in nanny!.qualities" :key="q">
              {{ qualities[q] || q }}
            </Badge>
          </div>
        </div>

        <div v-if="(nanny?.careers?.length ?? 0) > 0">
          <h4 class="font-semibold mb-2">Carreras</h4>
          <div class="flex flex-wrap gap-2">
            <Badge v-for="c in nanny!.careers" :key="c" variant="secondary">
              <Icon icon="lucide:graduation-cap" class="mr-1 h-3 w-3" />
              {{ careers[c] || c }}
            </Badge>
          </div>
        </div>

        <div v-if="(nanny?.courses?.length ?? 0) > 0">
          <h4 class="font-semibold mb-2">Cursos</h4>
          <div class="flex flex-wrap gap-2">
            <Badge v-for="c in nanny!.courses" :key="c" variant="outline">
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
