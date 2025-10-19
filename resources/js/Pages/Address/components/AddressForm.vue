<script setup lang="ts">
import { watch } from "vue"
import { FormControl, FormField, FormItem, FormMessage } from "@/components/ui/form"
import { Label } from "@/components/ui/label"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"

import { AddressFormService } from "@/services/AddressFormService"
import type { Address } from "@/types/Address"
import { TypeEnum } from "@/enums/addresses/type.enum"

// 🔸 Props polimórficas (REQUIRED)
const props = defineProps<{
  address?: Address
  ownerId: number              // id del dueño (Tutor|Nanny|Booking)
  ownerType: string            // FQCN, ej: "App\\Models\\Tutor"
}>()

const emit = defineEmits<{
  (e: "saved", payload?: any): void
}>()

// Instancia del servicio (polimórfico)
const formService = new AddressFormService({
  address: props.address,
  ownerId: props.ownerId,
  ownerType: props.ownerType,
})

const { errors, loading, saved } = formService

// Cuando guarde/edite, notificar al padre (el padre hace reload parcial)
watch(() => saved.value, (ok) => {
  if (!ok) return
  if (formService.savedAddress.value) {
    emit("saved", formService.savedAddress.value)
  } else {
    emit("saved", { ...values })
  }
})

// Submit
const submit = async () => {
  if (props.address?.id) {
    await formService.updateAddress()
  } else {
    await formService.saveAddress()
  }
}
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-8 mt-6">
    <!-- Código Postal -->
    <FormField v-slot="{ componentField }" name="postal_code">
      <FormItem>
        <Label>Código Postal</Label>
        <FormControl>
          <Input placeholder="Ej. 44100" v-bind="componentField" />
        </FormControl>
        <FormMessage>{{ errors['postal_code']?.[0] }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Calle -->
    <FormField v-slot="{ componentField }" name="street">
      <FormItem>
        <Label>Calle</Label>
        <FormControl>
          <Input placeholder="Ej. Av. Vallarta" v-bind="componentField" />
        </FormControl>
        <FormMessage>{{ errors['street']?.[0] }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Colonia -->
    <FormField v-slot="{ componentField }" name="neighborhood">
      <FormItem>
        <Label>Colonia</Label>
        <FormControl>
          <Input placeholder="Ej. Americana" v-bind="componentField" />
        </FormControl>
        <FormMessage>{{ errors['neighborhood']?.[0] }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Tipo de Dirección -->
    <FormField v-slot="{ componentField }" name="type">
      <FormItem>
        <Label>Tipo de Dirección</Label>
        <FormControl>
          <!-- ⚠️ Usa model-value / @update:model-value para evitar valores vacíos -->
          <Select
            :model-value="componentField.value"
            @update:model-value="componentField.onChange"
          >
            <SelectTrigger>
              <SelectValue placeholder="Selecciona un tipo" />
            </SelectTrigger>
            <SelectContent>
              <!-- TypeEnum.labels(): { [value]: label } -->
              <SelectItem
                v-for="(label, value) in TypeEnum.labels()"
                :key="String(value)"
                :value="String(value)"
              >
                {{ label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </FormControl>
        <FormMessage>{{ errors['type']?.[0] }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Otro Tipo -->
    <FormField v-slot="{ componentField }" name="other_type">
      <FormItem>
        <Label>Otro tipo (si aplica)</Label>
        <FormControl>
          <Input placeholder="Especifica otro tipo" v-bind="componentField" />
        </FormControl>
        <FormMessage>{{ errors['other_type']?.[0] }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Número Interno -->
    <FormField v-slot="{ componentField }" name="internal_number">
      <FormItem>
        <Label>Número Interno</Label>
        <FormControl>
          <Input placeholder="Ej. 2A" v-bind="componentField" />
        </FormControl>
        <FormMessage>{{ errors['internal_number']?.[0] }}</FormMessage>
      </FormItem>
    </FormField>
  </div>

  <!-- Botones -->
  <div class="mt-6 flex justify-end gap-2">
    <Button @click="submit" :disabled="loading">
      <span v-if="loading">Guardando...</span>
      <span v-else>{{ props.address ? "Actualizar" : "Guardar" }}</span>
    </Button>
  </div>
</template>
