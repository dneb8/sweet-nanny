<script setup lang="ts">
import { watch, ref, computed } from "vue"
import { FormControl, FormField, FormItem, FormMessage } from "@/components/ui/form"
import { Label } from "@/components/ui/label"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { useDebounceFn } from "@vueuse/core"

import { AddressFormService } from "@/services/AddressFormService"
import type { Address } from "@/types/Address"
import { TypeEnum } from "@/enums/addresses/type.enum"
import { useAwsLocation, type AddressSuggestion } from "@/composables/useAwsLocation"

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

const { errors, loading, saved, values, setFieldValue } = formService

// AWS Location autocomplete
const { loading: searchLoading, suggestions, searchPlaces, clearSuggestions } = useAwsLocation()
const searchQuery = ref('')
const showSuggestions = ref(false)
const selectedSuggestion = ref<AddressSuggestion | null>(null)

// Debounced search
const debouncedSearch = useDebounceFn(async (query: string) => {
  if (query && query.length >= 3) {
    await searchPlaces(query)
    showSuggestions.value = true
  } else {
    clearSuggestions()
    showSuggestions.value = false
  }
}, 500)

// Handle search input change
const onSearchInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  searchQuery.value = target.value
  debouncedSearch(target.value)
}

// Select a suggestion
const selectSuggestion = (suggestion: AddressSuggestion) => {
  selectedSuggestion.value = suggestion
  searchQuery.value = suggestion.fullAddress
  
  // Auto-fill form fields
  setFieldValue('street', suggestion.street)
  setFieldValue('neighborhood', suggestion.neighborhood)
  setFieldValue('postal_code', suggestion.postalCode)
  setFieldValue('latitude', suggestion.latitude)
  setFieldValue('longitude', suggestion.longitude)
  
  // Clear suggestions
  showSuggestions.value = false
  clearSuggestions()
}

// Computed to check if we should show suggestions
const hasSuggestions = computed(() => suggestions.value.length > 0 && showSuggestions.value)

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
  <div class="mb-6">
    <!-- Búsqueda con autocompletado -->
    <div class="relative">
      <Label>Buscar dirección</Label>
      <Input 
        :model-value="searchQuery"
        @input="onSearchInput"
        placeholder="Ej. Av. Vallarta 1234, Guadalajara"
        class="mt-1"
      />
      
      <!-- Loading indicator -->
      <div v-if="searchLoading" class="absolute right-3 top-10 text-muted-foreground">
        <span class="text-xs">Buscando...</span>
      </div>
      
      <!-- Suggestions dropdown -->
      <div 
        v-if="hasSuggestions" 
        class="absolute z-50 mt-1 w-full rounded-md border bg-popover text-popover-foreground shadow-md"
      >
        <div class="max-h-60 overflow-y-auto p-1">
          <button
            v-for="(suggestion, index) in suggestions"
            :key="index"
            @click="selectSuggestion(suggestion)"
            class="w-full text-left px-3 py-2 text-sm rounded-sm hover:bg-accent hover:text-accent-foreground cursor-pointer transition-colors"
          >
            <div class="font-medium">{{ suggestion.label }}</div>
            <div class="text-xs text-muted-foreground">
              {{ suggestion.neighborhood }}, {{ suggestion.city }}, {{ suggestion.state }}
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>

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
