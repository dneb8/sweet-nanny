<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from "@/components/ui/dialog"

const props = defineProps<{ show: boolean }>()
const emit = defineEmits(['close'])
</script>

<template>
  <Dialog :open="props.show" @update:open="(value) => { if (!value) emit('close') }">
    <DialogContent class="max-w-lg">
      <DialogHeader>
        <DialogTitle></DialogTitle>
        <DialogDescription></DialogDescription>
      </DialogHeader>

      <!-- Contenido dinámico -->
      <slot />
    </DialogContent>
  </Dialog>
</template>
