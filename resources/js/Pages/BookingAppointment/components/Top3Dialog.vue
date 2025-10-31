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
  top3: Nanny[]
  qualities: Record<string, string>
}>()

const emit = defineEmits<{
  (e: 'update:open', v: boolean): void
  (e: 'close'): void
  (e: 'see', n: Nanny): void
  (e: 'choose', id: string): void
}>()

const hasData = computed(() => (props.top3?.length ?? 0) > 0)

function close() {
  emit('update:open', false)
  emit('close')
}
function see(n: Nanny) {
  emit('see', n)
}
function choose(id: string) {
  emit('choose', id)
}
function initials(name: string) {
  return name.split(' ').map(s => s[0]).join('').toUpperCase().slice(0,2)
}
</script>

<template>
  <Dialog :open="open" @update:open="(v: boolean) => emit('update:open', v)">
    <DialogContent class="sm:max-w-4xl" @escape-key-down="close" @pointer-down-outside="close">
      <DialogHeader>
        <DialogTitle class="text-2xl">Niñeras con mayor coincidencia</DialogTitle>
        <DialogDescription>Estas son las niñeras más recomendadas para tu cita</DialogDescription>
      </DialogHeader>

      <div v-if="!hasData" class="text-center py-8 text-muted-foreground">
        <Icon icon="lucide:user-x" class="mx-auto h-12 w-12 mb-3 opacity-50" />
        <p>No hay niñeras disponibles en este horario.</p>
        <p class="text-sm mt-2">Intenta ajustar la hora o vuelve más tarde.</p>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-4 py-4">
        <!-- 2do lugar -->
        <div
          v-if="top3[1]"
          class="rounded-2xl border bg-card p-4 flex flex-col items-center text-center space-y-3 sm:mt-8"
        >
          <Avatar class="h-20 w-20">
            <AvatarImage :src="top3[1].profile_photo_url || undefined" />
            <AvatarFallback>{{ initials(top3[1].name) }}</AvatarFallback>
          </Avatar>
          <div>
            <h3 class="font-semibold">{{ top3[1].name }}</h3>
            <div class="flex flex-wrap gap-1 justify-center mt-2">
              <Badge
                v-for="q in top3[1].qualities.slice(0, 2)"
                :key="q"
                variant="secondary"
                class="text-[10px]"
              >
                {{ qualities[q] || q }}
              </Badge>
            </div>
          </div>
          <div class="flex gap-2 w-full">
            <Button size="sm" variant="outline" class="flex-1" @click="see(top3[1])">Ver más</Button>
            <Button size="sm" class="flex-1" @click="choose(top3[1].id)">Elegir</Button>
          </div>
        </div>

        <!-- 1er lugar -->
        <div class="rounded-2xl border-2 border-primary bg-card p-4 flex flex-col items-center text-center space-y-3 relative">
          <div class="absolute -top-3 left-1/2 -translate-x-1/2">
            <Badge class="bg-primary">Destacada</Badge>
          </div>
          <Avatar class="h-24 w-24 ring-2 ring-primary ring-offset-2">
            <AvatarImage :src="top3[0]?.profile_photo_url || undefined" />
            <AvatarFallback>{{ top3[0] ? initials(top3[0].name) : '—' }}</AvatarFallback>
          </Avatar>
          <div>
            <h3 class="font-semibold text-lg">{{ top3[0]?.name }}</h3>
            <div class="flex flex-wrap gap-1 justify-center mt-2">
              <Badge v-for="q in (top3[0]?.qualities ?? []).slice(0, 3)" :key="q" class="text-[10px]">
                {{ qualities[q] || q }}
              </Badge>
            </div>
          </div>
          <div class="flex gap-2 w-full">
            <Button size="sm" variant="outline" class="flex-1" @click="top3[0] && see(top3[0])">Ver más</Button>
            <Button size="sm" class="flex-1" @click="top3[0] && choose(top3[0].id)">Elegir</Button>
          </div>
        </div>

        <!-- 3er lugar -->
        <div
          v-if="top3[2]"
          class="rounded-2xl border bg-card p-4 flex flex-col items-center text-center space-y-3 sm:mt-8"
        >
          <Avatar class="h-20 w-20">
            <AvatarImage :src="top3[2].profile_photo_url || undefined" />
            <AvatarFallback>{{ initials(top3[2].name) }}</AvatarFallback>
          </Avatar>
          <div>
            <h3 class="font-semibold">{{ top3[2].name }}</h3>
            <div class="flex flex-wrap gap-1 justify-center mt-2">
              <Badge
                v-for="q in top3[2].qualities.slice(0, 2)"
                :key="q"
                variant="secondary"
                class="text-[10px]"
              >
                {{ qualities[q] || q }}
              </Badge>
            </div>
          </div>
          <div class="flex gap-2 w-full">
            <Button size="sm" variant="outline" class="flex-1" @click="see(top3[2])">Ver más</Button>
            <Button size="sm" class="flex-1" @click="choose(top3[2].id)">Elegir</Button>
          </div>
        </div>
      </div>

      <div class="flex justify-center">
        <Button variant="ghost" @click="close">
          <Icon icon="lucide:chevron-down" class="mr-2 h-4 w-4" />
          Ver más opciones
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>
