<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { defineProps, defineEmits } from 'vue'

defineProps<{
  title: string
  formComponent: any
  formProps?: Record<string, any>
  modelValue: boolean
}>()

const emit = defineEmits(['update:modelValue', 'saved'])

function handleSaved(payload: any) {
  emit('saved', payload)
  emit('update:modelValue', false)
}
</script>

<template>
  <Dialog :open="modelValue" @update:open="(val: boolean) => emit('update:modelValue', val)">
    <!-- Trigger opcional: se puede usar desde fuera -->
    <DialogTrigger as-child>
      <slot name="trigger" />
    </DialogTrigger>

    <DialogContent>
      <DialogHeader>
        <DialogTitle>{{ title }}</DialogTitle>
        <DialogDescription>
          <!-- Descripción opcional -->
          <slot name="description" />
        </DialogDescription>
      </DialogHeader>

      <!-- Aquí cargamos el formulario dinámico -->
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
