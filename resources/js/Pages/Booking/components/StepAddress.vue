<script setup lang="ts">
import { ref, computed } from "vue"
import axios from "axios"
import { route } from "ziggy-js"
import { useBoundField } from "@/services/bookingFormService"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { ScrollArea } from "@/components/ui/scroll-area"
import { Icon } from '@iconify/vue'

import FormModal from "@/components/common/FormModal.vue"
import DeleteModal from "@/components/common/DeleteModal.vue"
import AddressForm from "@/Pages/Address/components/AddressForm.vue"

import { TypeEnum } from "@/enums/addresses/type.enum"
import type { Address } from "@/types/Address"
import type { Tutor } from "@/types/Tutor"

const props = defineProps<{
  tutor: Tutor | null
  initialAddresses?: Address[] | null
}>()

// Enlazar selección al campo booking.address_id
const addressIdBF = useBoundField<number>('booking.address_id')
const addressId = addressIdBF.value

const selectedAddressId = computed<number | null>({
  get: () => (addressId.value ?? null) as number | null,
  set: (v) => { addressId.value = v == null ? (null as any) : Number(v) },
})

const isSelected = (id?: string | number | null) => id != null && Number(selectedAddressId.value) === Number(id)

/** Estado local */
const addresses  = ref<Address[]>(props.initialAddresses ?? [])
const tutorIdNum = computed<number>(() => Number(props.tutor?.id ?? 0))
const tutorType  = "App\\Models\\Tutor"

/** Helpers selección (1 única address) */
/** Crear / Editar (mismo patrón que children) */
const showFormModal = ref(false)
const formTitle     = ref("Agregar dirección")
const formAddress   = ref<Address | null>(null)

function openCreate() {
  formTitle.value = "Agregar dirección"
  formAddress.value = null
  showFormModal.value = true
}
function openEdit(a: Address) {
  formTitle.value = "Editar dirección"
  formAddress.value = a
  showFormModal.value = true
}

/** Al guardar desde AddressForm:
 *  - Inserta/actualiza en la lista local
 *  - Autoselecciona la address
 *  - Cierra modal
 */
function onAddressSaved(address: Address) {
  const i = addresses.value.findIndex(x => String(x.id) === String(address.id))
  if (i === -1) {
    addresses.value.unshift(address)
  } else {
    addresses.value[i] = address
  }
  selectedAddressId.value = Number(address.id ?? 0)
  showFormModal.value = false
}

/** Eliminar (mismo patrón que children) */
const showDeleteModal = ref(false)
const toDelete = ref<Address | null>(null)
function openDelete(a: Address) {
  toDelete.value = a
  showDeleteModal.value = true
}
async function confirmDelete() {
  if (!toDelete.value?.id) return
  try {
    await axios.delete(route("addresses.destroy", { address: String(toDelete.value.id) }), {
      headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
    })
    addresses.value = addresses.value.filter(x => String(x.id) !== String(toDelete.value?.id))
    if (Number(selectedAddressId.value) === Number(toDelete.value?.id)) selectedAddressId.value = null
  } finally {
    showDeleteModal.value = false
    toDelete.value = null
  }
}
</script>

<template>
  <Card class="border-none bg-transparent shadow-none">
    <CardHeader>
      <CardTitle class="flex items-center justify-between">
        <div class="flex flex-col gap-1">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:map-pin" /> Dirección del servicio
          </div>
          <p class="text-xs text-muted-foreground max-w-md font-light italic">
            Selecciona una dirección del tutor o crea una nueva. Solo puedes elegir una.
          </p>
        </div>
        <Button size="sm" variant="outline" type="button" @click="openCreate">
          <Icon icon="lucide:plus" /> Registrar Dirección
        </Button>
      </CardTitle>
    </CardHeader>

    <CardContent class="space-y-4">
      <ScrollArea>
        <div
          class="max-h-64 rounded border p-6"
          :class="addressIdBF.errorMessage?.value ? 'border-rose-300' : ''"
          :aria-invalid="!!addressIdBF.errorMessage?.value"
          aria-describedby="booking-address-error"
        >
          <div v-if="!addresses.length" class="text-xs text-muted-foreground">
            No hay direcciones registradas.
          </div>

          <div v-else class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-3">
            <div
              v-for="a in addresses"
              :key="a.id"
              class="relative rounded-md border p-3 transition select-none bg-background/50"
              :class="[
                isSelected(a.id) ? 'ring-2 ring-primary border-primary bg-primary/5' : 'hover:border-primary/50',
                'cursor-pointer'
              ]"
              role="button"
              tabindex="0"
              @click="selectedAddressId = Number(a.id)"
              @keydown.enter.prevent="selectedAddressId = Number(a.id)"
              @keydown.space.prevent="selectedAddressId = Number(a.id)"
              :aria-pressed="isSelected(a.id)"
            >
              <span
                v-if="isSelected(a.id)"
                class="absolute right-2 top-2 inline-flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-[11px] font-medium text-primary"
              >
                <Icon icon="lucide:check-circle" class="h-3.5 w-3.5" /> Seleccionada
              </span>

              <div class="pr-16">
                <div class="flex items-center gap-2">
                  <span class="font-medium truncate">{{ a.street }}</span>
                </div>
                <div class="mt-1 text-xs text-muted-foreground">
                  {{ a.neighborhood }} • CP {{ a.postal_code }}
                </div>
                <div class="mt-2 text-[11px] inline-flex items-center px-2 py-0.5 rounded bg-secondary">
                  {{ TypeEnum.labels()[a.type] || a.type }}
                </div>
              </div>

              <div class="absolute right-2 bottom-2 flex items-center gap-2">
                <Button
                  size="icon"
                  variant="ghost"
                  class="h-8 w-8"
                  type="button"
                  @click.stop="openEdit(a)"
                  :aria-label="`Editar ${a.street}`"
                >
                  <Icon icon="lucide:edit" class="h-4 w-4" />
                </Button>
                <Button
                  size="icon"
                  variant="destructive"
                  class="h-8 w-8"
                  type="button"
                  @click.stop="openDelete(a)"
                  :aria-label="`Eliminar ${a.street}`"
                >
                  <Icon icon="lucide:trash" class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </div>

          <div class="mt-2 flex items-center justify-end">
            <p
              v-if="addressIdBF.errorMessage?.value"
              id="booking-address-error"
              class="text-[11px] text-rose-600"
            >
              {{ addressIdBF.errorMessage?.value }}
            </p>
          </div>
        </div>
      </ScrollArea>
    </CardContent>
  </Card>

  <!-- Modal de formulario (igual que children) -->
  <FormModal
    v-model="showFormModal"
    :title="formTitle"
    :form-component="AddressForm"
    :form-props="{
      address: formAddress,
      ownerId: tutorIdNum,
      ownerType: tutorType
    }"
    @saved="onAddressSaved"
  />

  <!-- Modal de eliminación -->
  <DeleteModal
    v-model:show="showDeleteModal"
    title="dirección"
    :message="`¿Seguro que deseas eliminar la dirección ${toDelete?.street}?`"
    @confirm="confirmDelete"
  />
</template>
