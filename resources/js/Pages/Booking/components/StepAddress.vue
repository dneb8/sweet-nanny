<script setup lang="ts">
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { useBoundField } from "@/services/bookingFormService"

const postal_code = useBoundField<string>("address.postal_code")
const street = useBoundField<string>("address.street")
const neighborhood = useBoundField<string>("address.neighborhood")
const type = useBoundField<string>("address.type")
const other_type = useBoundField<string>("address.other_type")
const internal_number = useBoundField<string>("address.internal_number")
</script>

<template>
  <div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
      <Label for="postal_code">Código postal</Label>
      <Input id="postal_code" v-model="postal_code.value" placeholder="Ej. 44100" class="mt-1" />
      <p v-if="postal_code.errorMessage" class="text-xs text-red-500 mt-1">{{ postal_code.errorMessage }}</p>
    </div>
    <div>
      <Label for="street">Calle</Label>
      <Input id="street" v-model="street.value" class="mt-1" />
    </div>
    <div>
      <Label for="neighborhood">Colonia</Label>
      <Input id="neighborhood" v-model="neighborhood.value" class="mt-1" />
    </div>
    <div>
      <Label for="type">Tipo</Label>
      <Input id="type" v-model="type.value" placeholder="Casa / Departamento" class="mt-1" />
    </div>
    <div>
      <Label for="other_type">Otro tipo</Label>
      <Input id="other_type" v-model="other_type.value" class="mt-1" />
    </div>
    <div>
      <Label for="internal_number">Número interno</Label>
      <Input id="internal_number" v-model="internal_number.value" class="mt-1" />
    </div>
  </div>
</template>
