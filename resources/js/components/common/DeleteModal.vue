<script setup lang="ts">
import { ref, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogHeader, DialogFooter, DialogTitle } from '@/components/ui/dialog'
import { Icon } from '@iconify/vue'

const props = defineProps<{
  show: boolean
  message: string
  onConfirm: () => void
  onCancel?: () => void
}>()

const emit = defineEmits(['update:show'])

const isOpen = ref(props.show)

watch(() => props.show, (val) => {
  isOpen.value = val
})

const close = () => {
  emit('update:show', false)
  props.onCancel?.()
}

const confirmDelete = () => {
  props.onConfirm()
  close()
}
</script>

<template>
  <Dialog :open="isOpen" @update:open="emit('update:show', $event)">
    <DialogContent class="flex flex-col items-center text-center gap-4">
      <!-- Icono con luz circular difusa -->
      <div class="relative flex items-center justify-center">
        <!-- Luz difusa detrás -->
        <span
          class="absolute size-12 rounded-full bg-rose-600 opacity-50 blur-xl animate-pulse-glow"
        ></span>
        <!-- Icono encima -->
        <Icon icon="line-md:alert" width="48" height="48" class="text-rose-600 relative z-10" />
      </div>

      <!-- Título -->
      <DialogHeader class="mt-2">
        <DialogTitle class="text-lg font-semibold">Confirmar eliminación</DialogTitle>
      </DialogHeader>

      <!-- Mensaje -->
      <p class="text-sm text-muted-foreground">{{ message }}</p>

      <!-- Botones alineados siempre -->
      <DialogFooter class="flex flex-row justify-center gap-3 w-full mt-4">
        <Button variant="ghost" @click="close" class="flex-1">Cancelar</Button>
        <Button variant="destructive" @click="confirmDelete" class="flex-1">Eliminar</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
@keyframes glow {
  0%, 100% {
    opacity: 0.4;
    transform: scale(0.9);
  }
  50% {
    opacity: 0.8;
    transform: scale(1.1);
  }
}

.animate-pulse-glow {
  animation: glow 1.2s infinite ease-in-out;
}
</style>
