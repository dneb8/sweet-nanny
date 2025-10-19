<script setup lang="ts">
import { ref, computed } from "vue"
import { Icon } from "@iconify/vue"
import { useBoundField } from "@/services/bookingFormService"
import { Card, CardContent } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { ScrollArea } from "@/components/ui/scroll-area"
import FormModal from "@/components/common/FormModal.vue"
import AddressForm from "@/Pages/Address/components/AddressForm.vue"
import { TypeEnum } from "@/enums/addresses/type.enum"
import type { Address } from "@/types/Address"
import type { Tutor } from "@/types/Tutor"

const props = defineProps<{
  tutor: Tutor | null
  initialAddresses?: Address[]
}>()

const addressIdBF = useBoundField<number | null>("booking.address_id")
const addressId = addressIdBF.value

const addresses = ref<Address[]>(props.initialAddresses ?? [])
const tutorId = computed<number>(() => Number(props.tutor?.id ?? 0))
const tutorType = "App\\Models\\Tutor"

const selectedId = computed<number | null>({
  get: () => addressId.value,
  set: (v) => { 
    addressId.value = v
  },
})

const isSelected = (id?: number | null) => id != null && selectedId.value === id

function toggleSelect(id: number) {
  if (isSelected(id)) {
    selectedId.value = null
  } else {
    selectedId.value = id
  }
}

// Modal crear/editar
const showFormModal = ref(false)
const formTitle = ref("Agregar dirección")
const formAddress = ref<Address | undefined>(undefined)

function openCreate() {
  formTitle.value = "Agregar dirección"
  formAddress.value = undefined
  showFormModal.value = true
}

function openEdit(a: Address) {
  formTitle.value = "Editar dirección"
  formAddress.value = a
  showFormModal.value = true
}

function onAddressSaved() {
  showFormModal.value = false
  // The form emits saved event, we just need to reload addresses
  // In a real app, we'd fetch updated list or the service returns the address
  // For now, we'll reload the page to refresh the address list
  window.location.reload()
}

function onModalClose() {
  showFormModal.value = false
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex justify-between items-center">
      <div>
        <h3 class="text-lg font-semibold">Dirección del Servicio</h3>
        <p class="text-sm text-muted-foreground">
          Selecciona una dirección o crea una nueva
        </p>
      </div>
      <Button type="button" size="sm" variant="outline" @click="openCreate">
        <Icon icon="solar:add-circle-linear" class="w-4 h-4 mr-2" />
        Nueva dirección
      </Button>
    </div>

    <!-- Lista de direcciones -->
    <div v-if="addresses.length > 0" class="space-y-2">
      <ScrollArea class="h-[340px] w-full rounded-md border p-4">
        <div class="space-y-3">
          <Card
            v-for="addr in addresses"
            :key="addr.id"
            class="cursor-pointer transition-all hover:shadow-md relative"
            :class="[isSelected(addr.id) && 'ring-2 ring-primary bg-primary/5']"
            @click="toggleSelect(addr.id!)"
          >
            <CardContent class="p-4">
              <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3 flex-1">
                  <Icon 
                    :icon="isSelected(addr.id) ? 'solar:check-circle-bold' : 'solar:map-point-linear'" 
                    class="w-5 h-5 mt-0.5 flex-shrink-0"
                    :class="[isSelected(addr.id) && 'text-primary']"
                  />
                  <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm truncate">{{ addr.street }}</p>
                    <p class="text-xs text-muted-foreground mt-1">
                      {{ addr.neighborhood }}
                    </p>
                    <div class="flex items-center gap-2 mt-2">
                      <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-secondary">
                        CP {{ addr.postal_code }}
                      </span>
                      <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-secondary">
                        {{ TypeEnum.labels()[addr.type] || addr.type }}
                      </span>
                    </div>
                  </div>
                </div>
                <Button 
                  type="button" 
                  size="sm" 
                  variant="ghost"
                  class="h-8 w-8 p-0"
                  @click.stop="openEdit(addr)"
                >
                  <Icon icon="solar:pen-linear" class="w-4 h-4" />
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </ScrollArea>
    </div>

    <!-- Estado vacío -->
    <div v-else class="flex flex-col items-center justify-center py-12 text-center border rounded-lg">
      <Icon icon="solar:map-point-broken" class="w-16 h-16 text-muted-foreground/50 mb-4" />
      <h4 class="text-sm font-medium mb-2">No hay direcciones guardadas</h4>
      <p class="text-xs text-muted-foreground mb-4">
        Crea tu primera dirección para continuar
      </p>
      <Button type="button" size="sm" @click="openCreate">
        <Icon icon="solar:add-circle-linear" class="w-4 h-4 mr-2" />
        Crear dirección
      </Button>
    </div>

    <!-- Modal para crear/editar -->
    <FormModal 
      :show="showFormModal" 
      :title="formTitle"
      @close="onModalClose"
    >
      <AddressForm
        :address="formAddress"
        :owner-id="tutorId"
        :owner-type="tutorType"
        @saved="onAddressSaved"
      />
    </FormModal>
  </div>
</template>
