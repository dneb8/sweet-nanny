<script setup lang="ts">
import { FormControl, FormField, FormItem, FormMessage } from "@/components/ui/form"
import { Label } from "@/components/ui/label"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { watch, ref } from 'vue'
import { AddressFormService } from "@/services/addressFormService";
import { Nanny } from "@/types/Nanny"
import { Address } from "@/types/Address"
import { TypeEnum } from "@/Enums/Address/TypeEnum"

const props = defineProps<{
  address?: Address;
  nanny?: Nanny;
}>()

const emit = defineEmits(["saved"])

// Inicializar servicio con valores reactivos
const formService = new AddressFormService(
  props.address || {
    nanny_id: props.nanny?.id,
    postal_code: "",
    street: "",
    neighborhood: "",
    type: "",
    other_type: "",
    internal_number: "",
  }
);

const { errors, loading, saved, formData } = formService;

// Emitir saved
watch(() => saved.value, (value) => {
  if (value) emit("saved");
});

// Función de submit para crear o actualizar
const submit = async () => {
  console.log("Enviando formulario", formData.value);
  if (props.address?.id) {
    await formService.updateAddress();
  } else {
    await formService.saveAddress();
  }
};
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
        <FormMessage>{{ errors['postal_code'] ? errors['postal_code'][0] : '' }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Calle -->
    <FormField v-slot="{ componentField }" name="street">
      <FormItem>
        <Label>Calle</Label>
        <FormControl>
          <Input placeholder="Ej. Av. Vallarta" v-bind="componentField" />
        </FormControl>
        <FormMessage>{{ errors['street'] ? errors['street'][0] : '' }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Colonia -->
    <FormField v-slot="{ componentField }" name="neighborhood">
      <FormItem>
        <Label>Colonia</Label>
        <FormControl>
          <Input placeholder="Ej. Americana" v-bind="componentField" />
        </FormControl>
        <FormMessage>{{ errors['neighborhood'] ? errors['neighborhood'][0] : '' }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Tipo de Dirección -->
    <FormField v-slot="{ componentField }" name="type">
      <FormItem>
        <Label>Tipo de Dirección</Label>
        <FormControl>
          <Select v-bind="componentField">
            <SelectTrigger>
              <SelectValue placeholder="Selecciona un tipo" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="(label, value) in TypeEnum.labels()" :key="value" :value="value">
                {{ label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </FormControl>
        <FormMessage>{{ errors['type'] ? errors['type'][0] : '' }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Otro Tipo -->
    <FormField v-slot="{ componentField }" name="other_type">
      <FormItem>
        <Label>Otro tipo (si aplica)</Label>
        <FormControl>
          <Input placeholder="Especifica otro tipo" v-bind="componentField" />
        </FormControl>
        <FormMessage>{{ errors['other_type'] ? errors['other_type'][0] : '' }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Número Interno -->
    <FormField v-slot="{ componentField }" name="internal_number">
      <FormItem>
        <Label>Número Interno</Label>
        <FormControl>
          <Input placeholder="Ej. 2A" v-bind="componentField" />
        </FormControl>
        <FormMessage>{{ errors['internal_number'] ? errors['internal_number'][0] : '' }}</FormMessage>
      </FormItem>
    </FormField>
  </div>

  <!-- Botones -->
  <div class="mt-6 flex justify-end gap-2">
    <Button @click="submit" class="w-auto" :disabled="loading">
      <span v-if="loading">Guardando...</span>
      <span v-else>{{ props.address ? "Actualizar" : "Guardar" }}</span>
    </Button>
  </div>
</template>
