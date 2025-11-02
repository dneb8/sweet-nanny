<script setup lang="ts">
import { watch, ref, computed } from 'vue'
import { FormControl, FormField, FormItem, FormMessage } from '@/components/ui/form'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { useDebounceFn } from '@vueuse/core'

import { AddressFormService } from '@/services/AddressFormService'
import type { Address } from '@/types/Address'
import { TypeEnum } from '@/enums/addresses/type.enum'
import { useGooglePlaces, type AddressSuggestion } from '@/composables/useGooglePlaces'

const props = defineProps<{
  address?: Address
  ownerId: number
  ownerType: string
}>()

const emit = defineEmits<{ (e: 'saved', payload?: any): void }>()

// Servicio polimórfico
const formService = new AddressFormService({
  address: props.address,
  ownerId: props.ownerId,
  ownerType: props.ownerType,
})
const { errors, loading, saved, values, setFieldValue } = formService

// Google Places
const { loading: searchLoading, suggestions, searchPlaces, clearSuggestions } = useGooglePlaces()
const searchQuery = ref('')
const showSuggestions = ref(false)
const hasSelectedSuggestion = ref(false)

const debouncedSearch = useDebounceFn(async (query: string) => {
  if (query && query.length >= 3) {
    await searchPlaces(query)
    showSuggestions.value = true
  } else {
    clearSuggestions()
    showSuggestions.value = false
  }
}, 500)

const onSearchInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  searchQuery.value = target.value
  hasSelectedSuggestion.value = false // si vuelve a escribir, debe seleccionar de nuevo
  debouncedSearch(target.value)
}

const selectSuggestion = (s: AddressSuggestion) => {
  searchQuery.value = s.fullAddress

  // Auto-fill (solo lectura en UI)
  setFieldValue('street', s.street ?? '')
  setFieldValue('external_number', s.external_number ?? '')
  setFieldValue('neighborhood', s.neighborhood ?? '')
  setFieldValue('postal_code', s.postalCode ?? '')
  setFieldValue('municipality', s.municipality ?? '')
  setFieldValue('state', s.state ?? '')
  setFieldValue('latitude', s.latitude ?? null)
  setFieldValue('longitude', s.longitude ?? null)

  // Auto-generate name if empty
  if (!values.name || values.name.trim() === '') {
    let generatedName = ''
    
    // Priority: use place name if available from Google (for establishments)
    // Otherwise construct from address components
    if (s.label && s.label !== s.fullAddress) {
      // Format: "Place Name — Full Address"
      generatedName = `${s.label.split(',')[0]} — ${s.neighborhood || s.municipality || ''}`
    } else {
      // Format: "Street ExternalNumber — Neighborhood"
      const streetPart = s.street && s.external_number ? `${s.street} ${s.external_number}` : s.street || s.external_number || ''
      const neighborhoodPart = s.neighborhood || ''
      generatedName = `${streetPart} — ${neighborhoodPart}`.trim()
    }
    
    // Limit to 80 characters
    if (generatedName.length > 80) {
      generatedName = generatedName.substring(0, 77) + '...'
    }
    
    setFieldValue('name', generatedName)
  }

  // Los dos campos editables se quedan para el usuario:
  // internal_number (string) y type (enum)
  if (!values.type) setFieldValue('type', '') // asegura estado controlado

  hasSelectedSuggestion.value = true
  showSuggestions.value = false
  clearSuggestions()
}

// Guardar habilitado solo si: hay sugerencia + internos requeridos llenos
const isInternalNumberValid = computed(() => !!values.internal_number && String(values.internal_number).trim().length > 0)
const isTypeValid = computed(() => !!values.type && String(values.type).trim().length > 0)
const canSubmit = computed(() => hasSelectedSuggestion.value && isInternalNumberValid.value && isTypeValid.value && !loading.value)

watch(
  () => saved.value,
  (ok) => {
    if (!ok) return
    if (formService.savedAddress.value) {
      emit('saved', formService.savedAddress.value)
    } else {
      emit('saved', { ...values })
    }
  },
)

const submit = async () => {
  if (!canSubmit.value) return
  if (props.address?.id) {
    await formService.updateAddress()
  } else {
    await formService.saveAddress()
  }
}
</script>

<template>
  <div>
    <!-- Búsqueda forzada -->
    <div class="mb-6">
      <div class="relative space-y-2">
        <div class="flex gap-2 items-center">
            <Icon icon="icon-park-outline:search" width="18" height="18" />
            <Label class="text-base font-medium">Busca tu dirección</Label>
        </div>
        <Input
          :model-value="searchQuery"
          @input="onSearchInput"
          placeholder="Ej. Av. Vallarta 1234, Guadalajara"
          class="mt-1"
          aria-required="true"
        />

        <!-- Loading -->
        <div v-if="searchLoading" class="absolute right-3 top-10 text-muted-foreground">
          <span class="text-xs">Buscando...</span>
        </div>

        <!-- Sugerencias -->
        <div
          v-if="suggestions.length > 0 && showSuggestions"
          class="absolute z-50 mt-1 w-full rounded-md border bg-popover text-popover-foreground shadow-md"
        >
          <div class="max-h-60 overflow-y-auto p-1">
            <button
              v-for="(s, i) in suggestions"
              :key="i"
              @click="selectSuggestion(s)"
              class="w-full cursor-pointer rounded-sm px-3 py-2 text-left text-sm transition-colors hover:bg-accent hover:text-accent-foreground"
            >
              <div class="font-medium">{{ s.label }}</div>
              <div class="text-xs text-muted-foreground">
                {{ s.neighborhood }}, {{ s.municipality }}, {{ s.state }}
              </div>
            </button>
          </div>
        </div>
      </div>
      <p class="mt-2 text-xs text-muted-foreground">
        Elije una dirección dentro de la Zona Metropolitana de Guadalajara.
      </p>
    </div>

    <!-- Campos auto-completados (solo lectura) -->
    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2">
      <FormField v-slot="{ componentField }" name="street">
        <FormItem>
          <Label class="text-muted-foreground">Calle</Label>
          <FormControl>
            <Input v-bind="componentField" placeholder="Auto-completado" readonly disabled class="bg-muted" />
          </FormControl>
          <FormMessage>{{ errors['street']?.[0] }}</FormMessage>
        </FormItem>
      </FormField>

      <FormField v-slot="{ componentField }" name="external_number">
        <FormItem>
          <Label class="text-muted-foreground">Número Exterior</Label>
          <FormControl>
            <Input v-bind="componentField" placeholder="Auto-completado" readonly disabled class="bg-muted" />
          </FormControl>
          <FormMessage>{{ errors['external_number']?.[0] }}</FormMessage>
        </FormItem>
      </FormField>

      <FormField v-slot="{ componentField }" name="neighborhood">
        <FormItem>
          <Label class="text-muted-foreground">Colonia</Label>
          <FormControl>
            <Input v-bind="componentField" placeholder="Auto-completado" readonly disabled class="bg-muted" />
          </FormControl>
          <FormMessage>{{ errors['neighborhood']?.[0] }}</FormMessage>
        </FormItem>
      </FormField>

      <FormField v-slot="{ componentField }" name="postal_code">
        <FormItem>
          <Label class="text-muted-foreground">Código Postal</Label>
          <FormControl>
            <Input v-bind="componentField" placeholder="Auto-completado" readonly disabled class="bg-muted" />
          </FormControl>
          <FormMessage>{{ errors['postal_code']?.[0] }}</FormMessage>
        </FormItem>
      </FormField>

      <FormField v-slot="{ componentField }" name="municipality">
        <FormItem>
          <Label class="text-muted-foreground">Municipio</Label>
          <FormControl>
            <Input v-bind="componentField" placeholder="Auto-completado" readonly disabled class="bg-muted" />
          </FormControl>
        </FormItem>
      </FormField>

      <FormField v-slot="{ componentField }" name="state">
        <FormItem>
          <Label class="text-muted-foreground">Estado</Label>
          <FormControl>
            <Input v-bind="componentField" placeholder="Auto-completado" readonly disabled class="bg-muted" />
          </FormControl>
        </FormItem>
      </FormField>
    </div>

    <!-- Obligatorios EDITABLES al final -->
    <div class="mt-8 grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2">
      <FormField v-slot="{ componentField }" name="name">
        <FormItem>
          <Label>Nombre de la Dirección *</Label>
          <FormControl>
            <Input v-bind="componentField" placeholder="Ej: Mi Casa — Americana" maxlength="80" />
          </FormControl>
          <FormMessage>{{ errors['name']?.[0] }}</FormMessage>
        </FormItem>
      </FormField>
      <!-- Número Interno (obligatorio ahora) -->
      <FormField v-slot="{ componentField }" name="internal_number">
        <FormItem>
          <Label>Número Interno *</Label>
          <FormControl>
            <Input
              v-bind="componentField"
              placeholder="Ej. 2A"
              :disabled="!hasSelectedSuggestion"
            />
          </FormControl>
          <FormMessage>
            {{ errors['internal_number']?.[0] }}
            <template v-if="!errors['internal_number'] && !isInternalNumberValid && hasSelectedSuggestion">
              Debes ingresar el número interno.
            </template>
          </FormMessage>
        </FormItem>
      </FormField>

      <!-- Tipo de Dirección (obligatorio editable) -->
      <FormField v-slot="{ componentField }" name="type">
        <FormItem>
          <Label>Tipo de Dirección *</Label>
          <FormControl>
            <Select
              :model-value="componentField.value"
              @update:model-value="componentField.onChange"
            >
              <SelectTrigger class="w-full">
                <SelectValue placeholder="Selecciona un tipo" />
              </SelectTrigger>
              <SelectContent>
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
          <FormMessage>
            {{ errors['type']?.[0] }}
            <template v-if="!errors['type'] && !isTypeValid && hasSelectedSuggestion">
              Debes seleccionar un tipo.
            </template>
          </FormMessage>
        </FormItem>
      </FormField>
    </div>

    <!-- Botones -->
    <div class="mt-6 flex justify-end gap-2">
      <Button @click="submit" :disabled="!canSubmit">
        <span v-if="loading">Guardando...</span>
        <span v-else>{{ props.address ? 'Actualizar' : 'Guardar' }}</span>
      </Button>
    </div>
  </div>
</template>
