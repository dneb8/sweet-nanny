<script setup lang="ts">
import { ref } from "vue"
import {
  FormField, FormItem, FormLabel, FormControl, FormMessage,
} from "@/components/ui/form"
import { Input } from "@/components/ui/input"
import {
  Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
} from "@/components/ui/select"
import { Button } from "@/components/ui/button"
import { createAddress, updateAddress, type Owner } from "@/services/addressFormService"
import type { Address } from "@/types/Address"
import { TypeEnum } from "@/enums/addresses/type.enum"
import * as z from "zod"
import { toTypedSchema } from "@vee-validate/zod"
import { useForm } from "vee-validate"

const props = defineProps<{ 
  address?: Address
  ownerId: string | number
  ownerType: string
}>()

const emit = defineEmits<{ 
  (e: "saved", value: Address): void
  (e: "loading", value: boolean): void
}>()

const loading = ref(false)
const errores = ref<Record<string, string[]>>({})

const formSchema = toTypedSchema(
  z.object({
    postal_code: z.string().min(4, "Código postal requerido"),
    street: z.string().min(2, "Calle requerida"),
    neighborhood: z.string().min(2, "Colonia requerida"),
    type: z.string().min(2, "Tipo requerido"),
    other_type: z.string().optional(),
    internal_number: z.string().optional(),
  })
)

const { values, isFieldDirty, handleSubmit } = useForm({
  validationSchema: formSchema,
  initialValues: {
    postal_code: props.address?.postal_code ?? "",
    street: props.address?.street ?? "",
    neighborhood: props.address?.neighborhood ?? "",
    type: props.address?.type ?? "",
    other_type: props.address?.other_type ?? "",
    internal_number: props.address?.internal_number ?? "",
  },
})

const onSubmit = handleSubmit(async (formData) => {
  loading.value = true
  emit("loading", true)
  errores.value = {}

  const owner: Owner = {
    ownerId: props.ownerId,
    ownerType: props.ownerType,
  }

  try {
    let savedAddress: Address
    
    if (props.address?.id) {
      // Update existing address
      savedAddress = await updateAddress(props.address.id, formData, owner)
    } else {
      // Create new address
      savedAddress = await createAddress(formData, owner)
    }

    emit("saved", savedAddress)
  } catch (err: any) {
    if (err?.response?.data?.errors) {
      errores.value = err.response.data.errors
    }
  } finally {
    loading.value = false
    emit("loading", false)
  }
})

function onPostalCodeInput(e: Event) {
  const target = e.target as HTMLInputElement
  target.value = target.value.replace(/\D/g, "").slice(0, 6)
}
</script>
</script>

<template>
  <form class="max-w-md mx-auto space-y-4" @submit.prevent="onSubmit">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <!-- Postal Code -->
      <FormField v-slot="{ componentField }" name="postal_code" :validate-on-blur="!isFieldDirty">
        <FormItem>
          <FormLabel>Código Postal</FormLabel>
          <FormControl>
            <Input 
              placeholder="Ej. 44100" 
              v-bind="componentField" 
              @keydown.enter.stop
              @input="onPostalCodeInput"
              inputmode="numeric"
            />
          </FormControl>
          <FormMessage />
          <span v-if="errores['postal_code']" class="text-sm text-destructive">{{ errores['postal_code'][0] }}</span>
        </FormItem>
      </FormField>

      <!-- Type -->
      <FormField v-slot="{ value, handleChange }" name="type" :validate-on-blur="!isFieldDirty">
        <FormItem>
          <FormLabel>Tipo</FormLabel>
          <FormControl>
            <Select :model-value="value" @update:model-value="handleChange">
              <SelectTrigger class="h-9 w-full" type="button">
                <SelectValue placeholder="Selecciona" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="(label, val) in TypeEnum.labels()" :key="String(val)" :value="String(val)">
                  {{ label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </FormControl>
          <FormMessage />
          <span v-if="errores['type']" class="text-sm text-destructive">{{ errores['type'][0] }}</span>
        </FormItem>
      </FormField>
    </div>

    <!-- Street -->
    <FormField v-slot="{ componentField }" name="street" :validate-on-blur="!isFieldDirty">
      <FormItem>
        <FormLabel>Calle y número</FormLabel>
        <FormControl>
          <Input placeholder="Ej. Av. Hidalgo 123" v-bind="componentField" @keydown.enter.stop />
        </FormControl>
        <FormMessage />
        <span v-if="errores['street']" class="text-sm text-destructive">{{ errores['street'][0] }}</span>
      </FormItem>
    </FormField>

    <!-- Neighborhood -->
    <FormField v-slot="{ componentField }" name="neighborhood" :validate-on-blur="!isFieldDirty">
      <FormItem>
        <FormLabel>Colonia</FormLabel>
        <FormControl>
          <Input placeholder="Ej. Americana" v-bind="componentField" @keydown.enter.stop />
        </FormControl>
        <FormMessage />
        <span v-if="errores['neighborhood']" class="text-sm text-destructive">{{ errores['neighborhood'][0] }}</span>
      </FormItem>
    </FormField>

    <!-- Internal Number (optional) -->
    <FormField v-slot="{ componentField }" name="internal_number" :validate-on-blur="!isFieldDirty">
      <FormItem>
        <FormLabel>Número interno (opcional)</FormLabel>
        <FormControl>
          <Input placeholder="Ej. 4B, Apt 201" v-bind="componentField" @keydown.enter.stop />
        </FormControl>
        <FormMessage />
        <span v-if="errores['internal_number']" class="text-sm text-destructive">{{ errores['internal_number'][0] }}</span>
      </FormItem>
    </FormField>

    <!-- Other Type (optional) -->
    <FormField v-slot="{ componentField }" name="other_type" :validate-on-blur="!isFieldDirty">
      <FormItem>
        <FormLabel>Otro tipo (opcional)</FormLabel>
        <FormControl>
          <Input placeholder="Ej. Condominio, Residencial" v-bind="componentField" @keydown.enter.stop />
        </FormControl>
        <FormMessage />
        <span v-if="errores['other_type']" class="text-sm text-destructive">{{ errores['other_type'][0] }}</span>
      </FormItem>
    </FormField>

    <div class="mt-2 flex items-center justify-center gap-2">
      <Button size="sm" variant="outline" :disabled="loading" type="submit">
        {{ props.address?.id ? 'Actualizar' : 'Guardar' }}
      </Button>
      <span v-if="loading" class="text-xs text-muted-foreground">Guardando…</span>
    </div>
  </form>
</template>
