<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Icon } from '@iconify/vue'

defineProps<{
    show: boolean
}>()

const emit = defineEmits<{
    (e: 'close'): void
    (e: 'confirm'): void
}>()
</script>

<template>
    <Dialog :open="show" @update:open="(value) => !value && emit('close')">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Icon icon="lucide:alert-triangle" class="h-5 w-5 text-amber-500" />
                    Confirmar cambio de niñera
                </DialogTitle>
                <DialogDescription class="pt-2">
                    Estás a punto de cambiar la niñera asignada a esta cita. 
                    La niñera actual será notificada de que su asignación ha sido cancelada.
                </DialogDescription>
            </DialogHeader>

            <div class="py-2">
                <p class="text-sm text-muted-foreground">¿Deseas continuar con esta acción?</p>
            </div>

            <DialogFooter class="gap-2">
                <Button variant="outline" @click="emit('close')">Cancelar</Button>
                <Button variant="default" @click="emit('confirm')">Sí, cambiar niñera</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
