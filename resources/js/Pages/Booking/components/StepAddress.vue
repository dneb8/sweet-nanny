<script setup lang="ts">
import { ref, computed } from "vue"
import { Icon } from "@iconify/vue"
import axios from "axios"
import { route } from "ziggy-js"
import { useBoundField } from "@/services/bookingFormService"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { ScrollArea } from "@/components/ui/scroll-area"
import FormModal from "@/components/common/FormModal.vue"
import DeleteModal from "@/components/common/DeleteModal.vue"
import { TypeEnum } from "@/enums/addresses/type.enum"
import type { Address } from "@/types/Address"

const props = defineProps<{
  addresses?: Address[]
}>()

const addressIdBF = useBoundField<number | null>("booking.address_id")
const addressId = addressIdBF.value

// Address fields for inline creation/editing
const postal_code     = useBoundField<string>("address.postal_code")
const street          = useBoundField<string>("address.street")
const neighborhood    = useBoundField<string>("address.neighborhood")
const type            = useBoundField<string>("address.type")
const other_type      = useBoundField<string>("address.other_type")
const internal_number = useBoundField<string>("address.internal_number")

const addresses = ref<Address[]>(props.addresses ?? [])
const mode = ref<'select' | 'create' | 'edit'>('select')
const editingAddress = ref<Address | null>(null)

const selectedId = computed<number | null>({
  get: () => addressId.value,
  set: (v) => { addressId.value = v },
})

const isSelected = (id?: number | null) => id != null && selectedId.value === id

function selectAddress(id: number) {
  selectedId.value = id
  const addr = addresses.value.find(a => a.id === id)
  if (addr) {
    // Clear inline fields when selecting existing
    postal_code.value.value = ""
    street.value.value = ""
    neighborhood.value.value = ""
    type.value.value = ""
    other_type.value.value = ""
    internal_number.value.value = ""
  }
}

function createNew() {
  mode.value = 'create'
  selectedId.value = null
  editingAddress.value = null
  // Clear fields
  postal_code.value.value = ""
  street.value.value = ""
  neighborhood.value.value = ""
  type.value.value = ""
  other_type.value.value = ""
  internal_number.value.value = ""
}

function editAddress(addr: Address) {
  mode.value = 'edit'
  selectedId.value = null
  editingAddress.value = addr
  // Populate fields
  postal_code.value.value = addr.postal_code
  street.value.value = addr.street
  neighborhood.value.value = addr.neighborhood
  type.value.value = addr.type
  other_type.value.value = addr.other_type || ""
  internal_number.value.value = addr.internal_number || ""
}

function cancelInline() {
  mode.value = 'select'
  editingAddress.value = null
  // Clear fields
  postal_code.value.value = ""
  street.value.value = ""
  neighborhood.value.value = ""
  type.value.value = ""
  other_type.value.value = ""
  internal_number.value.value = ""
}

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
  <div class="space-y-4">
    <div class="flex justify-between items-center mb-4">
      <div>
        <h3 class="text-lg font-semibold">Dirección del Servicio</h3>
        <p class="text-sm text-muted-foreground">
          Selecciona una dirección existente o crea una nueva
        </p>
      </div>
      <Button v-if="mode === 'select'" type="button" size="sm" variant="outline" @click="createNew">
        <Icon icon="solar:add-circle-linear" class="w-4 h-4 mr-2" />
        Nueva
      </Button>
      <Button v-else type="button" size="sm" variant="ghost" @click="cancelInline">
        <Icon icon="solar:close-circle-linear" class="w-4 h-4 mr-2" />
        Cancelar
      </Button>
    </div>

    <!-- List of existing addresses (select mode) -->
    <div v-if="mode === 'select' && addresses.length > 0" class="space-y-2">
      <ScrollArea class="h-[300px] w-full rounded-md border p-4">
        <div class="space-y-2">
          <Card
            v-for="addr in addresses"
            :key="addr.id"
            class="cursor-pointer transition-all hover:shadow-md"
            :class="[isSelected(addr.id) && 'ring-2 ring-primary']"
          >
            <CardContent class="p-4">
              <div class="flex items-start justify-between">
                <div class="flex-1" @click="selectAddress(addr.id!)">
                  <div class="flex items-center gap-2 mb-2">
                    <Icon 
                      :icon="isSelected(addr.id) ? 'solar:check-circle-bold' : 'solar:map-point-linear'" 
                      class="w-5 h-5"
                      :class="[isSelected(addr.id) && 'text-primary']"
                    />
                    <span class="font-medium">{{ addr.street }}</span>
                  </div>
                  <div class="text-sm text-muted-foreground space-y-1">
                    <p>{{ addr.neighborhood }}, CP {{ addr.postal_code }}</p>
                    <p>{{ TypeEnum.labels()[addr.type] || addr.type }}</p>
                    <p v-if="addr.internal_number">Número interior: {{ addr.internal_number }}</p>
                  </div>
                </div>
                <Button type="button" size="sm" variant="ghost" @click="editAddress(addr)">
                  <Icon icon="solar:pen-linear" class="w-4 h-4" />
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </ScrollArea>
    </div>

    <!-- Empty state when no addresses -->
    <div v-if="mode === 'select' && addresses.length === 0" class="text-center py-8">
      <Icon icon="solar:map-point-broken" class="w-12 h-12 mx-auto mb-4 text-muted-foreground" />
      <p class="text-sm text-muted-foreground mb-4">No tienes direcciones guardadas</p>
      <Button type="button" size="sm" @click="createNew">
        <Icon icon="solar:add-circle-linear" class="w-4 h-4 mr-2" />
        Crear primera dirección
      </Button>
    </div>

    <!-- Inline create/edit form -->
    <div v-if="mode === 'create' || mode === 'edit'" class="space-y-4 border rounded-lg p-4 bg-muted/20">
      <h4 class="font-medium text-sm">{{ mode === 'create' ? 'Nueva Dirección' : 'Editar Dirección' }}</h4>
      
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
          />
          <p v-if="postal_code.errorMessage" class="text-xs text-red-500 mt-1">
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
          />
          <p v-if="street.errorMessage" class="text-xs text-red-500 mt-1">
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
          />
          <p v-if="neighborhood.errorMessage" class="text-xs text-red-500 mt-1">
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
          <Label for="other_type" class="text-sm">Otro tipo (opcional)</Label>
          <Input
            id="other_type"
            :class="cls()"
            v-model="other_type.value.value"
            placeholder="Ej. Condominio, Residencial"
          />
        </div>

        <!-- Número interno -->
        <div class="sm:col-span-2">
          <Label for="internal_number" class="text-sm">Número interno (opcional)</Label>
          <Input
            id="internal_number"
            :class="cls()"
            v-model="internal_number.value.value"
            placeholder="Ej. 4B, Apt 201"
            autocomplete="address-line3"
          />
        </div>
      </div>
    </div>
  </div>
</template>
