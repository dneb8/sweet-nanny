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
  (e: "saved", ...args: any[]): void
}>()

const slots = useSlots()

function close() {
  emit("update:modelValue", false)
}

function handleSaved(...args: any[]) {
  emit("saved", ...args)
  close()
}

function onOpenChange(val: boolean) {
  emit("update:modelValue", !!val)
}
</script>

<template>
  <Dialog :open="modelValue" @update:open="onOpenChange">
    <DialogTrigger v-if="slots.trigger" as-child>
      <slot name="trigger" />
    </DialogTrigger>

    <!-- 👇 Aquí está la versión corregida -->
    <DialogContent
      @escapeKeyDown="close"
      @interactOutside="close"
      class="sm:max-w-[425px] grid-rows-[auto_minmax(0,1fr)_auto] p-0 max-h-[90dvh]"
    >
      <!-- HEADER FIJO -->
      <DialogHeader class="p-6 pb-0">
        <DialogTitle>{{ title }}</DialogTitle>
        <DialogDescription>
          <slot name="description" />
        </DialogDescription>
      </DialogHeader>

      <!-- CONTENIDO SCROLLABLE -->
      <div class="grid gap-4 py-4 overflow-y-auto px-6">
        <component
          :is="formComponent"
          v-bind="formProps"
          @saved="handleSaved"
        />
      </div>

      <!-- FOOTER FIJO -->
      <DialogFooter class="p-6 pt-0">
        <slot name="footer" />
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
