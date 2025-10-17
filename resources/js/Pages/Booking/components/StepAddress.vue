<script setup lang="ts">
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { useBoundField } from "@/services/bookingFormService"
import { TypeEnum } from "@/enums/addresses/type.enum"

// Campos enlazados al form global (Vee-Validate)
const postal_code     = useBoundField<string>("address.postal_code")
const street          = useBoundField<string>("address.street")
const neighborhood    = useBoundField<string>("address.neighborhood")
const type            = useBoundField<string>("address.type")
const other_type      = useBoundField<string>("address.other_type")
const internal_number = useBoundField<string>("address.internal_number")

function onPostalCodeInput(e: Event) {
  const target = e.target as HTMLInputElement
  target.value = target.value.replace(/\D/g, "").slice(0, 6)
  postal_code.value.value = target.value
}

const cls = (err?: string) =>
  [
    "mt-1","h-9","w-full","bg-background","placeholder:text-muted-foreground",
    err ? "" : "",
  ].filter(Boolean).join(" ")
</script>

<template>
  <div class="grid gap-4 sm:grid-cols-2">
    <!-- Código postal -->
    <div class="sm:col-span-2">
      <Label for="postal_code" class="text-sm">Código postal</Label>
      <Input
        id="postal_code"
        :class="cls(postal_code.errorMessage.value)"
        v-model="postal_code.value.value"
        inputmode="numeric"
        autocomplete="postal-code"
        placeholder="Ej. 44100"
        @input="onPostalCodeInput"
        :aria-describedby="postal_code.errorMessage ? 'postal_code_error' : undefined"
      />
      <p v-if="postal_code.errorMessage" id="postal_code_error" class="text-xs text-red-500 mt-1">
        {{ postal_code.errorMessage }}
      </p>
    </div>

    <!-- Calle -->
    <div>
      <Label for="street" class="text-sm">Calle</Label>
      <Input
        id="street"
        :class="cls(street.errorMessage.value)"
        v-model="street.value.value"
        autocomplete="address-line1"
        placeholder="Ej. Av. Hidalgo 123"
        :aria-describedby="street.errorMessage ? 'street_error' : undefined"
      />
      <p v-if="street.errorMessage" id="street_error" class="text-xs text-red-500 mt-1">
        {{ street.errorMessage }}
      </p>
    </div>

    <!-- Colonia -->
    <div>
      <Label for="neighborhood" class="text-sm">Colonia</Label>
      <Input
        id="neighborhood"
        :class="cls(neighborhood.errorMessage.value)"
        v-model="neighborhood.value.value"
        autocomplete="address-line2"
        placeholder="Ej. Americana"
        :aria-describedby="neighborhood.errorMessage ? 'neighborhood_error' : undefined"
      />
      <p v-if="neighborhood.errorMessage" id="neighborhood_error" class="text-xs text-red-500 mt-1">
        {{ neighborhood.errorMessage }}
      </p>
    </div>

    <!-- Tipo (Select) -->
    <div>
      <Label class="text-sm">Tipo</Label>
      <Select v-model="type.value.value">
        <SelectTrigger class="mt-1 w-full">
          <SelectValue placeholder="Selecciona un tipo" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem
            v-for="(label, val) in TypeEnum.labels()"
            :key="String(val)"
            :value="String(val)"
          >
            {{ label }}
          </SelectItem>
        </SelectContent>
      </Select>
      <p v-if="type.errorMessage" class="text-xs text-red-500 mt-1">
        {{ type.errorMessage }}
      </p>
    </div>

    <!-- Otro tipo -->
    <div>
      <Label for="other_type" class="text-sm">Otro tipo</Label>
      <Input
        id="other_type"
        :class="cls()"
        v-model="other_type.value.value"
        placeholder="Ej. Condominio, Residencial, etc."
      />
    </div>

    <!-- Número interno -->
    <div>
      <Label for="internal_number" class="text-sm">Número interno</Label>
      <Input
        id="internal_number"
        :class="cls()"
        v-model="internal_number.value.value"
        placeholder="Ej. 4B"
        autocomplete="address-line3"
      />
    </div>
  </div>
</template>
