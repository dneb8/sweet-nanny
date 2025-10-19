<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog"
import { useSlots } from "vue"

defineProps<{
  title?: string
  formComponent: any
  formProps?: Record<string, any>
  modelValue: boolean
}>()

const emit = defineEmits<{
  (e: "update:modelValue", v: boolean): void
  // Reemitimos el/los argumentos que envíe el form (address, {address}, etc.)
  (e: "saved", ...args: any[]): void
}>()

const slots = useSlots()

function close() {
  emit("update:modelValue", false)
}

// Reemite TODO el payload del form y cierra el modal
function handleSaved(...args: any[]) {
  emit("saved", ...args)
  close()
}

// Mantén el two-way binding con Dialog (open)
function onOpenChange(val: boolean) {
  emit("update:modelValue", !!val)
}
</script>

<template>
  <Dialog :open="modelValue" @update:open="onOpenChange">
    <!-- Solo renderizamos el trigger si lo usas -->
    <DialogTrigger v-if="slots.trigger" as-child>
      <slot name="trigger" />
    </DialogTrigger>

    <DialogContent
      @escapeKeyDown="close"
      @interactOutside="close"
    >
      <DialogHeader>
        <DialogTitle>{{ title }}</DialogTitle>
        <DialogDescription>
          <slot name="description" />
        </DialogDescription>
      </DialogHeader>

      <!-- Form dinámico -->
      <component
        :is="formComponent"
        v-bind="formProps"
        @saved="handleSaved"
      />

      <DialogFooter>
        <slot name="footer" />
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
