<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import type { Nanny } from '@/types/Nanny'

// v-model:open
const open = defineModel<boolean>('open', { required: true })

const props = defineProps<{
  top3: Nanny[]
  qualities: Record<string, string>
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'see', n: Nanny): void
  (e: 'choose', id: string): void
}>()

const hasData = computed(() => (props.top3?.length ?? 0) > 0)

function close() {
  open.value = false
  emit('close')
}
function see(n: Nanny) {
  emit('see', n)
}
function choose(id: string) {
  emit('choose', id)
}
function initials(name: string) {
  return name
    .split(' ')
    .filter(Boolean)
    .map(s => s[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

// Glow helper: capa glow debajo, contenido arriba, sin bloquear clics
function topHighlightClasses(isTop: boolean) {
  if (!isTop) return ''
  return [
    'relative',
    'z-0', // no elevamos el stacking context de la tarjeta
    'rounded-2xl',
    // capa glow detrás, no intercepta clics
    'before:pointer-events-none',
    'before:absolute before:-inset-2 before:rounded-3xl',
    'before:z-0',
    'before:bg-gradient-to-r before:from-fuchsia-400/25 before:via-rose-400/30 before:to-purple-400/25',
    'before:blur-2xl before:content-[""]',
    // animación visible
    'animate-[glow_2.2s_ease-in-out_infinite]',
  ].join(' ')
}
</script>

<style scoped>
@keyframes glow {
  0%, 100% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0.25); transform: translateZ(0) scale(1);}
  50% { box-shadow: 0 0 40px 6px rgba(244, 63, 94, 0.35);  transform: translateZ(0) scale(1.2);  }
}
</style>

<template>
  <Dialog v-model:open="open">
    <!-- shadcn/ui ya usa portal con z-50; si personalizaste, puedes añadir class="z-[60]" -->
    <DialogContent class="sm:max-w-4xl">
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
          class="rounded-2xl border dark:bg-white/10 p-4 flex flex-col items-center text-center space-y-3 sm:mt-8"
          :class="topHighlightClasses(true)"
        >
          <Avatar class="h-20 w-20 relative z-10">
            <AvatarImage :src="top3[1].profile_photo_url || undefined" />
            <AvatarFallback>{{ initials(top3[1].name) }}</AvatarFallback>
          </Avatar>
          <div class="relative z-10">
            <h3 class="font-semibold">{{ top3[1].name }}</h3>
            <div class="flex flex-wrap gap-1 justify-center mt-2">
              <Badge
                v-for="q in top3[1].qualities.slice(0, 2)"
                :key="q"
                class="text-[10px] bg-purple-200 text-purple-900 dark:text-purple-200 dark:bg-purple-900/60 dark:border-purple-200"
              >
                {{ qualities[q] || q }}
              </Badge>
            </div>
          </div>
          <div class="relative z-10 flex gap-2 w-full">
            <Button size="sm" variant="outline" class="flex-1" @click="see(top3[1])">Ver perfil</Button>
            <Button size="sm" class="flex-1" @click="choose(top3[1].id)">Elegir</Button>
          </div>
        </div>

        <!-- 1er lugar -->
        <div
          class="rounded-2xl border-2 border-primary dark:bg-white/20 p-4 flex flex-col items-center text-center space-y-3"
          :class="topHighlightClasses(true)"
        >
          <div class="absolute -top-3 left-1/2 -translate-x-1/2 z-10">
            <Badge class="bg-primary">Destacada</Badge>
          </div>
          <Avatar class="h-24 w-24 ring-2 ring-primary ring-offset-2 relative z-10">
            <AvatarImage :src="top3[0]?.profile_photo_url || undefined" />
            <AvatarFallback>{{ top3[0] ? initials(top3[0].name) : '—' }}</AvatarFallback>
          </Avatar>
          <div class="relative z-10">
            <h3 class="font-semibold text-lg">{{ top3[0]?.name }}</h3>
            <div class="flex flex-wrap gap-1 justify-center mt-2">
              <Badge
                v-for="q in (top3[0]?.qualities ?? []).slice(0, 3)"
                :key="q"
                class="text-[10px] bg-purple-200 text-purple-900 dark:text-purple-200 dark:bg-purple-900/60 dark:border-purple-200"
              >
                {{ qualities[q] || q }}
              </Badge>
            </div>
          </div>
          <div class="relative z-10 flex gap-2 w-full">
            <Button size="sm" variant="outline" class="flex-1" @click="top3[0] && see(top3[0])">Ver perfil</Button>
            <Button size="sm" class="flex-1" @click="top3[0] && choose(top3[0].id)">Elegir</Button>
          </div>
        </div>

        <!-- 3er lugar -->
        <div
          v-if="top3[2]"
          class="rounded-2xl border dark:bg-white/10 p-4 flex flex-col items-center text-center space-y-3 sm:mt-8"
          :class="topHighlightClasses(true)"
        >
          <Avatar class="h-20 w-20 relative z-10">
            <AvatarImage :src="top3[2].profile_photo_url || undefined" />
            <AvatarFallback>{{ initials(top3[2].name) }}</AvatarFallback>
          </Avatar>
          <div class="relative z-10">
            <h3 class="font-semibold">{{ top3[2].name }}</h3>
            <div class="flex flex-wrap gap-1 justify-center mt-2">
              <Badge
                v-for="q in top3[2].qualities.slice(0, 2)"
                :key="q"
                class="text-[10px] bg-purple-200 text-purple-900 dark:text-purple-200 dark:bg-purple-900/60 dark:border-purple-200"
              >
                {{ qualities[q] || q }}
              </Badge>
            </div>
          </div>
          <div class="relative z-10 flex gap-2 w-full">
            <Button size="sm" variant="outline" class="flex-1" @click="see(top3[2])">Ver perfil</Button>
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
